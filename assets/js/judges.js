document.addEventListener("DOMContentLoaded", () => {
  // Search functionality
  const searchInput = document.getElementById("participant-search")
  const participantsTable = document.getElementById("participants-table")
  const participantRows = participantsTable.querySelectorAll("tbody tr")

  searchInput.addEventListener("input", function () {
    const searchTerm = this.value.toLowerCase()

    participantRows.forEach((row) => {
      const participantName = row.querySelector("td:nth-child(2)").textContent.toLowerCase()
      if (participantName.includes(searchTerm)) {
        row.style.display = ""
      } else {
        row.style.display = "none"
      }
    })
  })

  // Select participant functionality
  const selectButtons = document.querySelectorAll(".select-participant")
  const noParticipantSelected = document.getElementById("no-participant-selected")
  const participantScoring = document.getElementById("participant-scoring")
  const scoringDescription = document.getElementById("scoring-description")
  const selectedName = document.getElementById("selected-name")
  const selectedCategory = document.getElementById("selected-category")
  const selectedPoints = document.getElementById("selected-points")
  const participantIdInput = document.getElementById("participant-id")
  const scoreSlider = document.getElementById("score-slider")
  const scoreDisplay = document.getElementById("score-display")

  selectButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Get participant data from the row
      const row = this.closest("tr")
      const id = row.getAttribute("data-id")
      const name = row.getAttribute("data-name")
      const category = row.getAttribute("data-category")
      const points = row.getAttribute("data-points")

      // Highlight selected row
      participantRows.forEach((r) => r.classList.remove("selected"))
      row.classList.add("selected")

      // Update scoring panel
      noParticipantSelected.style.display = "none"
      participantScoring.style.display = "block"
      scoringDescription.textContent = `Scoring: ${name}`
      selectedName.textContent = name
      selectedCategory.textContent = category
      selectedPoints.textContent = points
      participantIdInput.value = id

      // Reset score slider to 50
      scoreSlider.value = 50
      scoreDisplay.textContent = "50"
    })
  })

  // Score slider functionality
  scoreSlider.addEventListener("input", function () {
    scoreDisplay.textContent = this.value
  })

  // Form submission with AJAX and real-time feedback
  const scoreForm = document.getElementById("score-form")

  scoreForm.addEventListener("submit", function (e) {
    e.preventDefault()

    const formData = new FormData(this)
    const submitButton = this.querySelector('button[type="submit"]')
    const originalText = submitButton.innerHTML

    // Show loading state
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...'
    submitButton.disabled = true

    fetch("../api/submit_score.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Show success message with animation
          const successMessage = document.createElement("div")
          successMessage.className = "success-message animated"
          successMessage.innerHTML = `
            <i class="fas fa-check-circle"></i>
            Score of ${scoreSlider.value} assigned to ${selectedName.textContent}!
          `

          const dashboardContainer = document.querySelector(".dashboard-container")
          dashboardContainer.insertBefore(successMessage, dashboardContainer.children[1])

          // Update the participant's score in the table with animation
          const participantRow = document.querySelector(`tr[data-id="${participantIdInput.value}"]`)
          const scoreCell = participantRow.querySelector("td:nth-child(4)")

          // Add update animation
          scoreCell.classList.add("score-updating")
          setTimeout(() => {
            scoreCell.textContent = Math.round(data.totalScore.average_score * 10) / 10
            participantRow.setAttribute("data-points", Math.round(data.totalScore.average_score * 10) / 10)
            scoreCell.classList.remove("score-updating")
            scoreCell.classList.add("score-updated")
            setTimeout(() => scoreCell.classList.remove("score-updated"), 1000)
          }, 200)

          // Update the displayed score
          selectedPoints.textContent = Math.round(data.totalScore.average_score * 10) / 10

          // Auto-hide success message after 3 seconds
          setTimeout(() => {
            successMessage.classList.add("fade-out")
            setTimeout(() => successMessage.remove(), 300)
          }, 3000)

          // Reset form
          setTimeout(() => {
            noParticipantSelected.style.display = "block"
            participantScoring.style.display = "none"
            scoringDescription.textContent = "Select a participant first"
            participantRows.forEach((r) => r.classList.remove("selected"))
          }, 1500)

          // Trigger real-time update for scoreboard viewers
          broadcastScoreUpdate(data)
        } else {
          showErrorMessage("Error: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        showErrorMessage("An error occurred while submitting the score.")
      })
      .finally(() => {
        // Reset button state
        submitButton.innerHTML = originalText
        submitButton.disabled = false
      })
  })

  // Function to show error messages
  function showErrorMessage(message) {
    const errorMessage = document.createElement("div")
    errorMessage.className = "error-message animated"
    errorMessage.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`

    const dashboardContainer = document.querySelector(".dashboard-container")
    dashboardContainer.insertBefore(errorMessage, dashboardContainer.children[1])

    setTimeout(() => {
      errorMessage.classList.add("fade-out")
      setTimeout(() => errorMessage.remove(), 300)
    }, 5000)
  }

  // Function to broadcast score updates (for real-time updates)
  function broadcastScoreUpdate(data) {
    // This could be enhanced with WebSockets for true real-time updates
    // For now, we'll rely on the polling mechanism in scoreboard.js
    console.log("Score updated:", data)
  }
})

// Toggle password visibility
document.querySelectorAll('.toggle-password').forEach(button => {
  button.addEventListener('click', function() {
      const input = this.parentElement.querySelector('input');
      const icon = this.querySelector('i');
      if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
          input.type = 'password';
          icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
  });
});

// Show modal if needed
if ($showChangePasswordModal) {
  document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('passwordModal').classList.add('active');
  });
}

document.addEventListener('DOMContentLoaded', function() {
  // Ensure all displayed points are valid numbers
  document.querySelectorAll('[data-points]').forEach(row => {
      const points = parseFloat(row.getAttribute('data-points'));
      const pointsCell = row.querySelector('td:nth-child(4)'); // Points column
      if (isNaN(points)) {
          pointsCell.textContent = '0'; // Fallback if NaN
          row.setAttribute('data-points', '0'); // Update data attribute
      }
  });

  // Handle score submission (AJAX example)
  const scoreForm = document.getElementById('score-form');
  if (scoreForm) {
      scoreForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          const participantId = formData.get('participant_id');
          const score = parseFloat(formData.get('score'));

          // Validate score (prevent NaN submission)
          if (isNaN(score) || score < 1 || score > 100) {
              alert('Please enter a valid score (1-100)');
              return;
          }

          // Submit via AJAX
          fetch(this.action, {
              method: 'POST',
              body: formData
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  // Update UI with new points (ensure no NaN)
                  const pointsCell = document.querySelector(`tr[data-id="${participantId}"] td:nth-child(4)`);
                  pointsCell.textContent = isNaN(data.total_points) ? '0' : data.total_points;
              } else {
                  alert(data.error || 'Failed to submit score');
              }
          })
          .catch(error => console.error('Error:', error));
      });
  }

  
});