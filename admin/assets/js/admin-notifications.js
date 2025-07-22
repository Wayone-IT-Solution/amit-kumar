// Admin Notifications Utility - Lightweight
class AdminNotification {
  constructor() {
    this.notifications = [];
    this.init();
  }

  init() {
    // Create notification container if it doesn't exist
    if (!document.getElementById('admin-notifications-container')) {
      const container = document.createElement('div');
      container.id = 'admin-notifications-container';
      document.body.appendChild(container);
    }
  }

  show(title, message, type = 'success', duration = 3000) {
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
    notification.className = `admin-notification ${type}`;
    
    notification.innerHTML = `
      <div class="notification-title">${title}</div>
      <div class="notification-message">${message}</div>
    `;

    // Add click to dismiss
    notification.addEventListener('click', () => {
      this.hide(notification);
    });

    document.getElementById('admin-notifications-container').appendChild(notification);
    return notification;
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
window.adminNotification = new AdminNotification();

// Shorthand functions for easy use
window.showAdminSuccess = (title, message, duration = 3000) => {
  return window.adminNotification.success(title, message, duration);
};

window.showAdminError = (title, message, duration = 3000) => {
  return window.adminNotification.error(title, message, duration);
};

window.showAdminWarning = (title, message, duration = 3000) => {
  return window.adminNotification.warning(title, message, duration);
};

window.showAdminInfo = (title, message, duration = 3000) => {
  return window.adminNotification.info(title, message, duration);
}; 