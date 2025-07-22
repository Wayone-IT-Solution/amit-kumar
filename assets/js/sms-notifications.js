// SMS Style Notifications Utility
class SMSNotification {
  constructor() {
    this.notifications = [];
    this.init();
  }

  init() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('sms-notifications-container')) {
      const container = document.createElement('div');
      container.id = 'sms-notifications-container';
      document.body.appendChild(container);
    }
  }

  show(title, message, type = 'success', duration = 4000) {
    const notification = this.createNotification(title, message, type);
    this.notifications.push(notification);
    
    // Show notification
    setTimeout(() => {
      notification.classList.add('show');
    }, 100);

    // Auto hide after duration
    setTimeout(() => {
      this.hide(notification);
    }, duration);

    return notification;
  }

  createNotification(title, message, type) {
    const notification = document.createElement('div');
    notification.className = `sms-notification ${type}`;
    
    const icon = this.getIcon(type);
    const time = new Date().toLocaleTimeString('en-US', { 
      hour: '2-digit', 
      minute: '2-digit',
      hour12: true 
    });

    notification.innerHTML = `
      <div class="sms-header">
        <div class="sms-icon">
          <i class="bi ${icon}"></i>
        </div>
        <h6 class="sms-title">${title}</h6>
      </div>
      <p class="sms-message">${message}</p>
      <div class="sms-time">${time}</div>
    `;

    // Add click to dismiss
    notification.addEventListener('click', () => {
      this.hide(notification);
    });

    document.getElementById('sms-notifications-container').appendChild(notification);
    return notification;
  }

  getIcon(type) {
    const icons = {
      success: 'bi-check-circle',
      error: 'bi-x-circle',
      warning: 'bi-exclamation-triangle',
      info: 'bi-info-circle'
    };
    return icons[type] || icons.success;
  }

  hide(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
      const index = this.notifications.indexOf(notification);
      if (index > -1) {
        this.notifications.splice(index, 1);
      }
    }, 300);
  }

  hideAll() {
    this.notifications.forEach(notification => {
      this.hide(notification);
    });
  }

  // Convenience methods
  success(title, message, duration) {
    return this.show(title, message, 'success', duration);
  }

  error(title, message, duration) {
    return this.show(title, message, 'error', duration);
  }

  warning(title, message, duration) {
    return this.show(title, message, 'warning', duration);
  }

  info(title, message, duration) {
    return this.show(title, message, 'info', duration);
  }
}

// Global instance
window.smsNotification = new SMSNotification();

// Shorthand functions for easy use
window.showSMS = (title, message, type = 'success', duration = 4000) => {
  return window.smsNotification.show(title, message, type, duration);
};

window.showSuccess = (title, message, duration = 4000) => {
  return window.smsNotification.success(title, message, duration);
};

window.showError = (title, message, duration = 4000) => {
  return window.smsNotification.error(title, message, duration);
};

window.showWarning = (title, message, duration = 4000) => {
  return window.smsNotification.warning(title, message, duration);
};

window.showInfo = (title, message, duration = 4000) => {
  return window.smsNotification.info(title, message, duration);
}; 