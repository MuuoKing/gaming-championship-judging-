// BattleJudge Gaming Tournament System JavaScript

document.addEventListener("DOMContentLoaded", () => {
    // Auto-refresh scoreboard every 30 seconds
    if (window.location.pathname.includes("scoreboard")) {
      setInterval(() => {
        refreshScoreboard()
      }, 30000)
    }
  
    // Form validation
    const forms = document.querySelectorAll("form")
    forms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        if (!validateForm(this)) {
          e.preventDefault()
        }
      })
    })
  
    // Add gaming effects
    addGamingEffects()
  })
  
  function refreshScoreboard() {
    fetch("/api/scoreboard.php")
      .then((response) => response.json())
      .then((data) => {
        updateScoreboardTable(data)
      })
      .catch((error) => {
        console.error("Error refreshing scoreboard:", error)
      })
  }
  
  function updateScoreboardTable(participants) {
    const tbody = document.querySelector("#scoreboard-table tbody")
    if (!tbody) return
  
    tbody.innerHTML = ""
  
    participants.forEach((participant, index) => {
      const row = createScoreboardRow(participant, index)
      tbody.appendChild(row)
    })
  }
  
  function createScoreboardRow(participant, index) {
    const row = document.createElement("tr")
    row.className = getRowClass(index)
  
    row.innerHTML = `
          <td>
              <div style="display: flex; align-items: center; gap: 8px;">
                  <span style="width: 32px; text-align: center; font-weight: bold;">${index + 1}</span>
                  ${getMedalIcon(index)}
              </div>
          </td>
          <td>
              <div class="${getRankClass(index)}" style="font-weight: bold; font-size: 18px;">
                  ${participant.name}
              </div>
              <div style="font-size: 12px; color: #9ca3af;">${participant.category}</div>
          </td>
          <td>
              <span class="badge ${participant.category === "Pre-registered" ? "badge-primary" : "badge-secondary"}">
                  ${participant.category}
              </span>
          </td>
          <td style="text-align: right;">
              <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                  <div class="progress-bar" style="width: 120px;">
                      <div class="progress-fill" style="width: ${participant.total_points}%;"></div>
                  </div>
                  <span class="${getRankClass(index)}" style="font-family: monospace; font-size: 20px; font-weight: bold;">
                      ${participant.total_points}
                  </span>
              </div>
          </td>
      `
  
    return row
  }
  
  function getRowClass(index) {
    if (index === 0) return "rank-1-bg"
    if (index === 1) return "rank-2-bg"
    if (index === 2) return "rank-3-bg"
    return ""
  }
  
  function getRankClass(index) {
    if (index === 0) return "rank-1"
    if (index === 1) return "rank-2"
    if (index === 2) return "rank-3"
    return ""
  }
  
  function getMedalIcon(index) {
    if (index === 0) return '<i class="fas fa-crown rank-1"></i>'
    if (index === 1) return '<i class="fas fa-medal rank-2"></i>'
    if (index === 2) return '<i class="fas fa-shield-alt rank-3"></i>'
    return '<i class="fas fa-fire" style="color: #7c3aed; opacity: 0.5;"></i>'
  }
  
  function validateForm(form) {
    const inputs = form.querySelectorAll("input[required]")
    let isValid = true
  
    inputs.forEach((input) => {
      if (!input.value.trim()) {
        showFieldError(input, "This field is required")
        isValid = false
      } else {
        clearFieldError(input)
      }
    })
  
    return isValid
  }
  
  function showFieldError(input, message) {
    clearFieldError(input)
  
    const errorDiv = document.createElement("div")
    errorDiv.className = "field-error"
    errorDiv.style.color = "#ef4444"
    errorDiv.style.fontSize = "14px"
    errorDiv.style.marginTop = "4px"
    errorDiv.textContent = message
  
    input.parentNode.appendChild(errorDiv)
    input.style.borderColor = "#ef4444"
  }
  
  function clearFieldError(input) {
    const existingError = input.parentNode.querySelector(".field-error")
    if (existingError) {
      existingError.remove()
    }
    input.style.borderColor = ""
  }
  
  function addGamingEffects() {
    // Add hover effects to cards
    const cards = document.querySelectorAll(".gaming-card, .card")
    cards.forEach((card) => {
      card.addEventListener("mouseenter", function () {
        this.style.transform = "translateY(-2px)"
        this.style.boxShadow = "0 0 25px rgba(123, 97, 255, 0.4)"
      })
  
      card.addEventListener("mouseleave", function () {
        this.style.transform = "translateY(0)"
        this.style.boxShadow = "0 0 15px rgba(123, 97, 255, 0.2)"
      })
    })
  
    // Add click effects to buttons
    const buttons = document.querySelectorAll(".btn")
    buttons.forEach((button) => {
      button.addEventListener("click", function (e) {
        const ripple = document.createElement("span")
        ripple.style.position = "absolute"
        ripple.style.borderRadius = "50%"
        ripple.style.background = "rgba(255, 255, 255, 0.3)"
        ripple.style.transform = "scale(0)"
        ripple.style.animation = "ripple 0.6s linear"
        ripple.style.left = e.clientX - e.target.offsetLeft + "px"
        ripple.style.top = e.clientY - e.target.offsetTop + "px"
  
        this.style.position = "relative"
        this.style.overflow = "hidden"
        this.appendChild(ripple)
  
        setTimeout(() => {
          ripple.remove()
        }, 600)
      })
    })
  }
  
  // Score assignment modal functionality
  function openScoreModal(participantId, participantName) {
    const modal = document.getElementById("scoreModal")
    const participantNameSpan = document.getElementById("modalParticipantName")
    const participantIdInput = document.getElementById("modalParticipantId")
  
    if (modal && participantNameSpan && participantIdInput) {
      participantNameSpan.textContent = participantName
      participantIdInput.value = participantId
      modal.style.display = "flex"
    }
  }
  
  function closeScoreModal() {
    const modal = document.getElementById("scoreModal")
    if (modal) {
      modal.style.display = "none"
    }
  }
  
  // Close modal when clicking outside
  window.addEventListener("click", (e) => {
    const modal = document.getElementById("scoreModal")
    if (modal && e.target === modal) {
      closeScoreModal()
    }
  })
  
  // Real-time score updates for progress bars
  function updateProgressBar(input) {
    const value = Number.parseInt(input.value) || 0
    const progressBar = input.parentNode.querySelector(".progress-fill")
    if (progressBar) {
      progressBar.style.width = value + "%"
    }
  }
  