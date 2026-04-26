function applyStoredTheme() {
  const theme = localStorage.getItem("theme");
  if (theme === "dark") {
    document.body.classList.add("theme-dark");
  }
}

function toggleTheme() {
  document.body.classList.toggle("theme-dark");
  const isDark = document.body.classList.contains("theme-dark");
  localStorage.setItem("theme", isDark ? "dark" : "light");
}

function appendChatMessage(panel, role, content) {
  const row = document.createElement("div");
  row.className = role === "bot" ? "chat-row bot" : "chat-row user";
  row.textContent = content;
  panel.appendChild(row);
  panel.scrollTop = panel.scrollHeight;
  return row;
}

async function sendChatMessage(chatPanel, input) {
  const message = input.value.trim();
  if (!message) {
    return;
  }

  appendChatMessage(chatPanel, "user", "You: " + message);
  input.value = "";
  const typingRow = appendChatMessage(chatPanel, "bot", "Assistant is typing...");

  try {
    const response = await fetch("api/chatbot_reply.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        message: message,
        csrf_token: window.CHATBOT_CSRF_TOKEN || "",
      }),
    });

    if (typingRow) {
      typingRow.remove();
    }

    const payload = await response.json();
    if (!response.ok) {
      appendChatMessage(chatPanel, "bot", "Assistant: " + (payload.error || "Could not process request."));
      return;
    }

    appendChatMessage(chatPanel, "bot", "Assistant: " + payload.reply);
  } catch (error) {
    if (typingRow) {
      typingRow.remove();
    }
    appendChatMessage(chatPanel, "bot", "Assistant: Network issue. Please retry.");
  }
}

function setupChatbot() {
  const chatPanel = document.getElementById("chat-panel");
  const chatForm = document.getElementById("chat-form");
  const chatInput = document.getElementById("chat-input");
  const quickButtons = document.querySelectorAll(".chat-quick");

  if (!chatPanel || !chatForm || !chatInput) {
    return;
  }

  appendChatMessage(chatPanel, "bot", "Assistant: Hello. Ask a farming question to start.");

  chatForm.addEventListener("submit", function (event) {
    event.preventDefault();
    sendChatMessage(chatPanel, chatInput);
  });

  quickButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      chatInput.value = button.dataset.message || "";
      sendChatMessage(chatPanel, chatInput);
    });
  });
}

function animateStats() {
  const values = document.querySelectorAll(".stat-value");
  values.forEach(function (node) {
    const rawText = node.textContent.trim();
    const numberMatch = rawText.match(/([0-9,.]+)/);
    if (!numberMatch) {
      return;
    }
    const target = parseFloat(numberMatch[1].replace(/,/g, ""));
    if (isNaN(target)) {
      return;
    }

    const prefix = rawText.slice(0, numberMatch.index);
    let current = 0;
    const duration = 700;
    const step = Math.max(1, target / (duration / 16));

    const timer = setInterval(function () {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      node.textContent = prefix + Math.round(current).toLocaleString();
    }, 16);
  });
}

function setupScrollReveal() {
  const targets = document.querySelectorAll(".hero-panel, .panel, .stat-card, .product-card, .auth-card");
  if (!targets.length) {
    return;
  }

  if (!("IntersectionObserver" in window)) {
    targets.forEach(function (node) {
      node.classList.add("is-visible");
    });
    return;
  }

  const observer = new IntersectionObserver(
    function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          obs.unobserve(entry.target);
        }
      });
    },
    { rootMargin: "0px 0px -60px 0px", threshold: 0.12 }
  );

  targets.forEach(function (node) {
    node.classList.add("reveal");
    observer.observe(node);
  });
}

function setupFormFeedback() {
  const forms = document.querySelectorAll("form");
  forms.forEach(function (form) {
    if (form.id === "chat-form") {
      return;
    }

    form.addEventListener("submit", function () {
      const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!submitBtn || submitBtn.classList.contains("danger-btn")) {
        return;
      }

      const originalText = submitBtn.textContent;
      submitBtn.dataset.originalText = originalText;
      submitBtn.textContent = "Please wait...";
      submitBtn.classList.add("loading-btn");
      submitBtn.disabled = true;
    });
  });
}

function digitsOnly(id, maxLength) {
  const node = document.getElementById(id);
  if (!node) {
    return;
  }

  node.addEventListener("input", function () {
    const cleaned = node.value.replace(/\D/g, "").slice(0, maxLength);
    if (cleaned !== node.value) {
      node.value = cleaned;
    }
  });
}

function setupInputFormatting() {
  digitsOnly("phone", 10);
  digitsOnly("pincode", 6);

  const textareas = document.querySelectorAll("textarea");
  textareas.forEach(function (area) {
    const resize = function () {
      area.style.height = "auto";
      area.style.height = Math.min(area.scrollHeight, 220) + "px";
    };
    area.addEventListener("input", resize);
    resize();
  });
}

function goToInfo() {
  window.location.href = "importance.php";
}

function toggleDark() {
  toggleTheme();
}

document.addEventListener("DOMContentLoaded", function () {
  applyStoredTheme();
  setupChatbot();
  animateStats();
  setupScrollReveal();
  setupFormFeedback();
  setupInputFormatting();
});