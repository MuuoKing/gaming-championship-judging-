document.addEventListener("DOMContentLoaded", () => {
  let refreshCount = 0
  const refreshCountElement = document.getElementById("refresh-count")
  const rankingsContainer = document.getElementById("rankings-container")
  let lastUpdateTime = 0

  // Function to create a judge score item
  function createJudgeScoreItem(score) {
    // Add null/undefined checks
    if (!score || typeof score.score === 'undefined') {
      return ''
    }

    let judgeScoreClass = ""
    if (score.score >= 90) judgeScoreClass = "judge-score-excellent"
    else if (score.score >= 80) judgeScoreClass = "judge-score-good"
    else if (score.score >= 70) judgeScoreClass = "judge-score-average"
    else judgeScoreClass = "judge-score-below-average"

    return `
      <div class="judge-score-item" data-judge-id="${score.judge_id || ''}">
        <div class="judge-info">
          <span class="judge-name">${score.display_name || 'Unknown'}</span>
          <span class="judge-username">@${score.username || 'unknown'}</span>
        </div>
        <div class="judge-score ${judgeScoreClass}">
          ${score.score}
        </div>
        <div class="score-timestamp">
          ${score.created_at ? new Date(score.created_at).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
          }) : 'Unknown date'}
        </div>
      </div>
    `
  }

  // Function to update a single participant's scores
  function updateParticipantScores(participant, judgeScores) {
    if (!participant || !participant.id) return

    const participantElement = document.querySelector(`[data-participant-id="${participant.id}"]`)
    if (!participantElement) return

    // Update main score
    const scoreElement = participantElement.querySelector(".score")
    if (!scoreElement) return

    const oldScore = Number.parseFloat(scoreElement.textContent) || 0
    const newScore = participant.totalPoints || 0

    if (oldScore !== newScore) {
      // Add animation class for score change
      scoreElement.classList.add("score-updating")
      setTimeout(() => {
        scoreElement.textContent = newScore
        scoreElement.classList.remove("score-updating")
        scoreElement.classList.add("score-updated")
        setTimeout(() => scoreElement.classList.remove("score-updated"), 1000)
      }, 200)
    }

    // Update score class
    let scoreClass = ""
    if (newScore >= 90) scoreClass = "score-excellent"
    else if (newScore >= 80) scoreClass = "score-good"
    else if (newScore >= 70) scoreClass = "score-average"
    else scoreClass = "score-below-average"

    scoreElement.className = `score ${scoreClass}`

    // Update judge count and total with null checks
    const judgeCountElement = participantElement.querySelector(".judge-count")
    if (judgeCountElement) {
      const judgeCount = participant.judgeCount || 0
      judgeCountElement.textContent = `${judgeCount} judge${judgeCount !== 1 ? "s" : ""}`
    }

    const totalScoreElement = participantElement.querySelector(".total-score")
    if (totalScoreElement) {
      totalScoreElement.textContent = `Total: ${participant.accumulatedScore || 0}`
    }

    // Update progress bar
    const progressBar = participantElement.querySelector(".progress")
    if (progressBar) {
      const progressWidth = Math.min(100, newScore)
      progressBar.style.width = `${progressWidth}%`
    }

    // Update judge scores
    const judgeScoresGrid = participantElement.querySelector(".judge-scores-grid")
    if (judgeScoresGrid && Array.isArray(judgeScores) && judgeScores.length > 0) {
      // Clear existing scores
      judgeScoresGrid.innerHTML = ""

      // Add updated scores
      judgeScores.forEach((score) => {
        judgeScoresGrid.innerHTML += createJudgeScoreItem(score)
      })
    }
  }

  // Function to create a complete participant ranking item
  function createParticipantItem(participant, index, judgeScores) {
    if (!participant) return ''

    let medalClass = ""
    if (index === 0) medalClass = "gold"
    else if (index === 1) medalClass = "silver"
    else if (index === 2) medalClass = "bronze"

    const totalPoints = participant.totalPoints || 0
    let scoreClass = ""
    if (totalPoints >= 90) scoreClass = "score-excellent"
    else if (totalPoints >= 80) scoreClass = "score-good"
    else if (totalPoints >= 70) scoreClass = "score-average"
    else scoreClass = "score-below-average"

    const progressWidth = Math.min(100, totalPoints)

    let judgeScoresHtml = ""
    if (Array.isArray(judgeScores) && judgeScores.length > 0) {
      judgeScoresHtml = `
        <div class="judge-scores-breakdown">
          <h4 class="breakdown-title">
            <i class="fas fa-gavel"></i> Judge Scores
          </h4>
          <div class="judge-scores-grid">
            ${judgeScores.map((score) => createJudgeScoreItem(score)).join("")}
          </div>
        </div>
      `
    } else {
      judgeScoresHtml = `
        <div class="no-scores">
          <i class="fas fa-clock"></i>
          <span>No scores submitted yet</span>
        </div>
      `
    }

    return `
      <div class="ranking-item ${medalClass}" data-participant-id="${participant.id || ''}">
        <div class="ranking-content">
          <div class="medal">
            <i class="fas fa-medal"></i>
          </div>
          <div class="participant-info">
            <div class="participant-header">
              <div class="participant-details">
                <h3 class="participant-name">${participant.name || 'Unknown Participant'}</h3>
                <p class="participant-category">${participant.category || 'Unknown Category'}</p>
              </div>
              <div class="score-container">
                <div class="score ${scoreClass}">${totalPoints}</div>
                <div class="score-info">
                  <span class="judge-count">${participant.judgeCount || 0} judge${(participant.judgeCount || 0) !== 1 ? "s" : ""}</span>
                  <span class="total-score">Total: ${participant.accumulatedScore || 0}</span>
                </div>
              </div>
            </div>
            ${judgeScoresHtml}
            <div class="progress-bar">
              <div class="progress" style="width: ${progressWidth}%"></div>
            </div>
          </div>
        </div>
      </div>
    `
  }

  // Function to check for score updates
  function checkForUpdates() {
    fetch(`api/get_updates.php?since=${lastUpdateTime}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        if (data && data.success && data.hasUpdates) {
          lastUpdateTime = data.timestamp || Date.now()
          updateScoreboard(true) // Force update

          // Show notification for new scores
          if (data.newScores && Array.isArray(data.newScores) && data.newScores.length > 0) {
            showScoreNotification(data.newScores)
          }
        }
      })
      .catch((error) => {
        console.error("Error checking for updates:", error)
      })
  }

  // Function to show score notification
  function showScoreNotification(newScores) {
    if (!Array.isArray(newScores)) return

    const notification = document.createElement("div")
    notification.className = "score-notification"
    notification.innerHTML = `
      <div class="notification-content">
        <i class="fas fa-star"></i>
        <span>New score${newScores.length > 1 ? "s" : ""} submitted!</span>
      </div>
    `

    document.body.appendChild(notification)

    // Animate in
    setTimeout(() => notification.classList.add("show"), 100)

    // Remove after 3 seconds
    setTimeout(() => {
      notification.classList.remove("show")
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove()
        }
      }, 300)
    }, 3000)
  }

  // Function to update the scoreboard
  function updateScoreboard(isRealTimeUpdate = false) {
    if (!rankingsContainer) {
      console.error("Rankings container not found")
      return
    }

    fetch("api/get_scores.php?type=average")
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.json()
      })
      .then((data) => {
        if (!data || !data.success) {
          throw new Error("Invalid response from server")
        }

        if (!Array.isArray(data.participants)) {
          console.warn("No participants data found")
          rankingsContainer.innerHTML = `
            <div class="no-participants">
              <i class="fas fa-info-circle"></i>
              <span>No participants found</span>
            </div>
          `
          return
        }

        if (!isRealTimeUpdate && refreshCountElement) {
          refreshCount++
          refreshCountElement.textContent = `Last updated: ${refreshCount} ${refreshCount === 1 ? "time" : "times"}`
        }

        // Get all judge scores for all participants - FIXED: Uncommented and corrected
        /*const participantPromises = data.participants.map((participant) =>
          fetch(`api/get_judge_scores.php?participant_id=${participant.id}`)
            .then((response) => {
              if (!response.ok) {
                console.warn(`Failed to fetch judge scores for participant ${participant.id}`)
                return { participant, judgeScores: [] }
              }
              return response.json()
            })
            .then((judgeData) => ({
              participant,
              judgeScores: (judgeData && judgeData.success && Array.isArray(judgeData.scores)) ? judgeData.scores : [],
            }))
            .catch((error) => {
              //console.error(`Error fetching judge scores for participant ${participant.id}:`, error)
              return { participant, judgeScores: [] }
            })
        )*/

        Promise.all(participantPromises).then((participantsWithScores) => {
          const existingParticipants = document.querySelectorAll("[data-participant-id]")

          if (existingParticipants.length === 0) {
            // Initial load - create all elements
            rankingsContainer.innerHTML = ""
            participantsWithScores.forEach(({ participant, judgeScores }, index) => {
              const itemHtml = createParticipantItem(participant, index, judgeScores)
              if (itemHtml) {
                rankingsContainer.innerHTML += itemHtml
              }
            })
          } else {
            // Update existing elements or reorder if needed
            const currentOrder = Array.from(existingParticipants).map((el) => {
              const id = el.getAttribute("data-participant-id")
              return id ? Number.parseInt(id) : null
            }).filter(id => id !== null)
            
            const newOrder = participantsWithScores.map(({ participant }) => participant.id)

            if (JSON.stringify(currentOrder) !== JSON.stringify(newOrder)) {
              // Reorder needed - rebuild with animation
              rankingsContainer.style.opacity = "0.7"
              setTimeout(() => {
                rankingsContainer.innerHTML = ""
                participantsWithScores.forEach(({ participant, judgeScores }, index) => {
                  const itemHtml = createParticipantItem(participant, index, judgeScores)
                  if (itemHtml) {
                    rankingsContainer.innerHTML += itemHtml
                  }
                })
                rankingsContainer.style.opacity = "1"
              }, 200)
            } else {
              // Just update scores
              participantsWithScores.forEach(({ participant, judgeScores }) => {
                updateParticipantScores(participant, judgeScores)
              })
            }
          }
        })
      })
      .catch((error) => {
        console.error("Error updating scoreboard:", error)
        if (rankingsContainer) {
          rankingsContainer.innerHTML = `
            <div class="error-message">
              <i class="fas fa-exclamation-triangle"></i>
              <span>Error loading scoreboard: ${error.message}</span>
              <button onclick="updateScoreboard()" class="retry-button">Retry</button>
            </div>
          `
        }
      })
  }

  // Make updateScoreboard globally available for retry button
  window.updateScoreboard = updateScoreboard

  // Initial load
  //updateScoreboard()

  // Set up periodic updates (every 3 seconds for full refresh)
  setInterval(updateScoreboard, 100000)

  // Set up real-time update checking (every 10 seconds)
  setInterval(checkForUpdates, 10000)
})
