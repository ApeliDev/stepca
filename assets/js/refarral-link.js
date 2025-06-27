function copyReferralLink() {
    const referralLink = document.getElementById('referral-link');
    referralLink.select();
    document.execCommand('copy');
    
    // Create and show the popup
    showCopySuccessPopup();
}

function showCopySuccessPopup() {
    // Remove existing popup if any
    const existingPopup = document.querySelector('.copy-success-popup');
    if (existingPopup) {
        existingPopup.remove();
    }

    // Create popup HTML
    const popup = document.createElement('div');
    popup.className = 'copy-success-popup';
    popup.innerHTML = `
        <div class="popup-content">
            <div class="popup-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="popup-text">
                <h3>Link Copied!</h3>
                <p>Referral link has been copied to clipboard</p>
            </div>
        </div>
    `;

    // Add CSS styles
    const style = document.createElement('style');
    style.textContent = `
        .copy-success-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            transform: translateX(400px);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            min-width: 300px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .copy-success-popup.show {
            transform: translateX(0);
        }

        .popup-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .popup-icon {
            font-size: 24px;
            color: #d1fae5;
            animation: checkPulse 0.6s ease-out;
        }

        .popup-text h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-weight: 600;
        }

        .popup-text p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        @keyframes checkPulse {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .copy-success-popup.fade-out {
            transform: translateX(400px);
            opacity: 0;
        }

        /* Mobile responsive */
        @media (max-width: 480px) {
            .copy-success-popup {
                right: 10px;
                left: 10px;
                min-width: auto;
                transform: translateY(-100px);
            }
            .copy-success-popup.show {
                transform: translateY(0);
            }
            .copy-success-popup.fade-out {
                transform: translateY(-100px);
            }
        }
    `;

    // Add styles to document if not already added
    if (!document.querySelector('#copy-popup-styles')) {
        style.id = 'copy-popup-styles';
        document.head.appendChild(style);
    }

    // Add popup to body
    document.body.appendChild(popup);

    // Trigger animation
    setTimeout(() => {
        popup.classList.add('show');
    }, 100);

    // Auto remove after 3 seconds
    setTimeout(() => {
        popup.classList.add('fade-out');
        setTimeout(() => {
            if (popup.parentNode) {
                popup.parentNode.removeChild(popup);
            }
        }, 400);
    }, 3000);

    // Add click to dismiss
    popup.addEventListener('click', () => {
        popup.classList.add('fade-out');
        setTimeout(() => {
            if (popup.parentNode) {
                popup.parentNode.removeChild(popup);
            }
        }, 400);
    });
}