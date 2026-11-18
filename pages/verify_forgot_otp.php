<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

if (!isset($_SESSION['reset_contact'])) {
    redirect('forgot_password.php');
}

$contact = $_SESSION['reset_contact'];
?>

<div class="form-container">
    <h2>Verify Reset OTP</h2>
    <div id="otp-timer-container" style="max-width: 300px; margin: 0 auto 1.5rem auto;">
        <div id="otp-timer" class="text-center mb-1" style="color: var(--primary-color); font-weight: bold; font-size: 1.1rem;">
            Time Remaining: <span id="timer">02:00</span>
        </div>
        <div style="width: 100%; height: 6px; background: #eee; border-radius: 10px; overflow: hidden;">
            <div id="timer-bar" style="width: 100%; height: 100%; background: var(--primary-color); transition: width 1s linear;"></div>
        </div>
    </div>
    
    <form action="../actions/verify_forgot_otp_action.php" method="POST">
        <div class="form-group text-center">
            <label for="otp">Enter 6-Digit OTP</label>
            <input type="text" id="otp" name="otp" required maxlength="6" style="font-size: 1.5rem; text-align: center; letter-spacing: 5px; max-width: auto; margin: 0 auto;">
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Verify OTP</button>
    </form>
    
    <div style="margin-top: 1.5rem; text-align: center;">
        <p style="margin-top: 1rem;">
            Didn't receive the code? 
            <a href="../actions/forgot_password_action.php?resend=1&contact=<?php echo urlencode($contact); ?>" id="resend-link" class="form-link" style="display:inline; margin:0; font-weight:bold; color: grey; pointer-events: none; opacity: 0.6;">Resend OTP</a>
        </p>
    </div>
</div>

<script>
    let timeLeft = 120; // 2 minutes in seconds
    const totalTime = 120;
    const timerDisplay = document.getElementById('timer');
    const timerBar = document.getElementById('timer-bar');
    const resendLink = document.getElementById('resend-link');
    
    const countdown = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerDisplay.innerHTML = "Expired";
            timerDisplay.style.color = "red";
            timerBar.style.width = "0%";
            timerBar.style.background = "red";
            resendLink.style.color = "var(--primary-color)";
            resendLink.style.pointerEvents = "auto";
            resendLink.style.opacity = "1";
        } else {
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            timerDisplay.innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            let percentage = (timeLeft / totalTime) * 100;
            timerBar.style.width = percentage + "%";
        }
        timeLeft -= 1;
    }, 1000);
</script>

<?php require_once '../includes/footer.php'; ?>
