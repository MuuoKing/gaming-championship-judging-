document.addEventListener("DOMContentLoaded", () => {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll(".tab-button")
    const tabContents = document.querySelectorAll(".tab-content")
  
    tabButtons.forEach((button) => {
      button.addEventListener("click", () => {
        // Remove active class from all buttons and contents
        tabButtons.forEach((btn) => btn.classList.remove("active"))
        tabContents.forEach((content) => content.classList.remove("active"))
  
        // Add active class to clicked button and corresponding content
        button.classList.add("active")
        const tabId = button.getAttribute("data-tab")
        document.getElementById(`${tabId}-tab`).classList.add("active")
      })
    })
  
    // Auto-hide success message after 3 seconds
    const successMessage = document.querySelector(".success-message")
    if (successMessage) {
      setTimeout(() => {
        successMessage.style.display = "none"
      }, 3000)
    }


    const usernameInput = document.getElementById('username');
    if (usernameInput) {
        usernameInput.addEventListener('blur', async () => {
            const username = usernameInput.value.trim();
            if (username.length > 0) {
                try {
                    const response = await fetch('/api/check_username.php?username=' + encodeURIComponent(username));
                    const data = await response.json();
                    
                    /*if (data.exists) {
                        alert('Username already taken. Please choose another.');
                        usernameInput.focus();
                    }*/
                } catch (error) {
                    console.error('Error checking username:', error);
                }
            }
        });
    }// 
    
        // Clear Scores Modal functionality
        const clearScoresBtn = document.getElementById('clearScoresBtn');
        const clearScoresModal = document.getElementById('clearScoresModal');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        const confirmClearBtn = document.getElementById('confirmClearScores');
        const clearScoresForm = document.querySelector('.clear-scores-form');
    
        if (clearScoresBtn && clearScoresModal) {
            // Open modal when button is clicked
            clearScoresBtn.addEventListener('click', () => {
                clearScoresModal.style.display = 'block';
            });
    
            // Close modal when X or Cancel is clicked
            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    clearScoresModal.style.display = 'none';
                });
            });
    
            // Close modal when clicking outside the modal content
            window.addEventListener('click', (event) => {
                if (event.target === clearScoresModal) {
                    clearScoresModal.style.display = 'none';
                }
            });
    
            // Handle confirmation
            confirmClearBtn.addEventListener('click', () => {
                clearScoresModal.style.display = 'none';
                clearScoresForm.submit();
            });
    
            // Close modal with Escape key
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && clearScoresModal.style.display === 'block') {
                    clearScoresModal.style.display = 'none';
                }
            });
    
          }
          // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
      button.addEventListener('click', function() {
          const passwordInput = this.previousElementSibling;
          const icon = this.querySelector('i');
          
          if (passwordInput.type === 'password') {
              passwordInput.type = 'text';
              icon.classList.remove('fa-eye');
              icon.classList.add('fa-eye-slash');
          } else {
              passwordInput.type = 'password';
              icon.classList.remove('fa-eye-slash');
              icon.classList.add('fa-eye');
          }
      });
      });
        
  })
  