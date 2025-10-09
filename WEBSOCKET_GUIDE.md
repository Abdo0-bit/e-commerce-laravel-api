# WebSocket Real-time Integration Guide

This guide explains how to integrate real-time features into your frontend application using Laravel Reverb WebSockets.

## Quick Setup

### 1. Install Frontend Dependencies

```bash
npm install --save laravel-echo pusher-js
```

### 2. Configure Laravel Echo

Create an `echo.js` configuration file:

```javascript
// echo.js
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: "reverb",
    key: process.env.VITE_REVERB_APP_KEY || "mbdu19pekmgbq2mzpd89",
    wsHost: process.env.VITE_REVERB_HOST || "localhost",
    wsPort: process.env.VITE_REVERB_PORT || 8080,
    forceTLS: (process.env.VITE_REVERB_SCHEME || "http") === "https",
    enabledTransports: ["ws", "wss"],

    // For authenticated channels
    auth: {
        headers: {
            Authorization: "Bearer " + localStorage.getItem("auth_token"),
        },
    },
});

export default echo;
```

### 3. Initialize in Your App

```javascript
// main.js or app.js
import echo from "./echo.js";

// Make Echo globally available
window.Echo = echo;
```

## Real-time Features Implementation

### 1. Shopping Cart Synchronization

Keep cart synchronized across all user's open tabs/devices:

```javascript
class CartManager {
    constructor(cartId) {
        this.cartId = cartId;
        this.setupRealTimeListeners();
    }

    setupRealTimeListeners() {
        // Listen for cart updates
        window.Echo.channel(`cart.${this.cartId}`).listen(
            ".cart.updated",
            (event) => {
                this.handleCartUpdate(event);
            }
        );
    }

    handleCartUpdate(event) {
        console.log("Cart updated in real-time:", event);

        // Update cart UI
        this.updateCartDisplay(event.cart_data);

        // Show notification
        this.showNotification(
            `Cart updated: ${event.cart_data.total_items} items`
        );

        // Update cart badge
        this.updateCartBadge(event.cart_data.total_items);
    }

    updateCartDisplay(cartData) {
        // Update your cart UI with new data
        const cartElement = document.getElementById("cart-items");
        if (cartElement) {
            cartElement.innerHTML = this.renderCartItems(cartData.items);
        }

        // Update total
        const totalElement = document.getElementById("cart-total");
        if (totalElement) {
            totalElement.textContent = `$${cartData.total_amount}`;
        }
    }

    updateCartBadge(itemCount) {
        const badge = document.getElementById("cart-badge");
        if (badge) {
            badge.textContent = itemCount;
            badge.style.display = itemCount > 0 ? "block" : "none";
        }
    }

    showNotification(message) {
        // Show toast notification or update UI
        if ("Notification" in window && Notification.permission === "granted") {
            new Notification("Cart Updated", { body: message });
        }
    }

    renderCartItems(items) {
        return items
            .map(
                (item) => `
            <div class="cart-item">
                <span>${item.name}</span>
                <span>Qty: ${item.quantity}</span>
                <span>$${item.total}</span>
            </div>
        `
            )
            .join("");
    }
}

// Initialize cart manager
const cartId = sessionStorage.getItem("cart_id") || "guest_" + Date.now();
const cartManager = new CartManager(cartId);
```

### 2. Order Status Updates

Show real-time order status updates to customers:

```javascript
class OrderTracker {
    constructor(userId) {
        this.userId = userId;
        this.setupOrderListeners();
    }

    setupOrderListeners() {
        // Listen on private user channel
        window.Echo.private(`user.${this.userId}`)
            .listen(".order.created", (event) => {
                this.handleNewOrder(event);
            })
            .listen(".order.status.updated", (event) => {
                this.handleOrderStatusUpdate(event);
            });
    }

    handleNewOrder(event) {
        console.log("New order created:", event);

        this.showNotification(
            "Order Confirmed!",
            `Your order #${event.order_id} has been received and is being processed.`,
            "success"
        );

        // Update orders list if visible
        this.updateOrdersList();
    }

    handleOrderStatusUpdate(event) {
        console.log("Order status updated:", event);

        const statusMessages = {
            processing: "Your order is now being processed",
            shipped: "Great news! Your order has been shipped",
            delivered: "Your order has been delivered",
            cancelled: "Your order has been cancelled",
        };

        this.showNotification(
            "Order Update",
            `Order #${event.order_id}: ${statusMessages[event.new_status]}`,
            this.getStatusType(event.new_status)
        );

        // Update specific order status in UI
        this.updateOrderStatus(event.order_id, event.new_status);
    }

    updateOrderStatus(orderId, newStatus) {
        const statusElement = document.getElementById(
            `order-${orderId}-status`
        );
        if (statusElement) {
            statusElement.textContent = newStatus.toUpperCase();
            statusElement.className = `status status-${newStatus}`;
        }
    }

    getStatusType(status) {
        const types = {
            processing: "info",
            shipped: "success",
            delivered: "success",
            cancelled: "error",
        };
        return types[status] || "info";
    }

    showNotification(title, message, type = "info") {
        // Create custom notification UI
        const notification = document.createElement("div");
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <h4>${title}</h4>
            <p>${message}</p>
            <button onclick="this.parentElement.remove()">×</button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    updateOrdersList() {
        // Refresh orders list
        fetch("/api/client/orders", {
            headers: {
                Authorization: "Bearer " + localStorage.getItem("auth_token"),
                Accept: "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                // Update orders display
                this.renderOrders(data.data);
            });
    }
}

// Initialize for authenticated users
const userId = getCurrentUserId(); // Your function to get current user ID
if (userId) {
    const orderTracker = new OrderTracker(userId);
}
```

### 3. Admin Real-time Dashboard

Real-time admin notifications for new orders:

```javascript
class AdminDashboard {
    constructor() {
        this.setupAdminListeners();
        this.orderSound = new Audio("/sounds/new-order.mp3"); // Optional
    }

    setupAdminListeners() {
        // Listen on private admin channel
        window.Echo.private("admin.orders")
            .listen(".order.created", (event) => {
                this.handleNewOrderForAdmin(event);
            })
            .listen(".order.status.updated", (event) => {
                this.handleOrderStatusForAdmin(event);
            });
    }

    handleNewOrderForAdmin(event) {
        console.log("New order for admin:", event);

        // Play notification sound
        this.playNotificationSound();

        // Show desktop notification
        this.showDesktopNotification(
            "New Order Received!",
            `Order #${event.order_id} from ${event.customer_name} - $${event.total_amount}`
        );

        // Update dashboard counters
        this.updateDashboardStats();

        // Add to recent orders list
        this.addToRecentOrders(event);

        // Flash notification in UI
        this.flashNewOrderIndicator();
    }

    handleOrderStatusForAdmin(event) {
        console.log("Order status updated for admin:", event);

        // Update orders table if visible
        this.updateOrderInTable(event.order_id, event.new_status);

        // Update dashboard if status affects counters
        if (
            event.new_status === "delivered" ||
            event.new_status === "cancelled"
        ) {
            this.updateDashboardStats();
        }
    }

    playNotificationSound() {
        if (this.orderSound) {
            this.orderSound.play().catch((e) => {
                console.log("Could not play notification sound:", e);
            });
        }
    }

    showDesktopNotification(title, body) {
        if ("Notification" in window) {
            if (Notification.permission === "granted") {
                new Notification(title, {
                    body: body,
                    icon: "/icon-192x192.png",
                    badge: "/badge-72x72.png",
                });
            } else if (Notification.permission !== "denied") {
                Notification.requestPermission().then((permission) => {
                    if (permission === "granted") {
                        new Notification(title, { body: body });
                    }
                });
            }
        }
    }

    flashNewOrderIndicator() {
        const indicator = document.getElementById("new-order-indicator");
        if (indicator) {
            indicator.style.display = "block";
            indicator.classList.add("flash-animation");

            setTimeout(() => {
                indicator.classList.remove("flash-animation");
            }, 3000);
        }
    }

    updateDashboardStats() {
        // Fetch updated dashboard stats
        fetch("/api/admin/dashboard", {
            headers: {
                Authorization: "Bearer " + localStorage.getItem("auth_token"),
                Accept: "application/json",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                this.renderDashboardStats(data.data);
            });
    }

    addToRecentOrders(orderEvent) {
        const recentOrdersList = document.getElementById("recent-orders");
        if (recentOrdersList) {
            const orderElement = document.createElement("div");
            orderElement.className = "recent-order new-order";
            orderElement.innerHTML = `
                <div class="order-info">
                    <span class="order-id">#${orderEvent.order_id}</span>
                    <span class="customer">${orderEvent.customer_name}</span>
                    <span class="amount">$${orderEvent.total_amount}</span>
                    <span class="status">${orderEvent.status}</span>
                </div>
            `;

            recentOrdersList.insertBefore(
                orderElement,
                recentOrdersList.firstChild
            );

            // Remove highlight after 5 seconds
            setTimeout(() => {
                orderElement.classList.remove("new-order");
            }, 5000);
        }
    }

    updateOrderInTable(orderId, newStatus) {
        const orderRow = document.getElementById(`order-row-${orderId}`);
        if (orderRow) {
            const statusCell = orderRow.querySelector(".status-cell");
            if (statusCell) {
                statusCell.textContent = newStatus.toUpperCase();
                statusCell.className = `status-cell status-${newStatus}`;
            }
        }
    }
}

// Initialize admin dashboard for admin users
if (isAdmin()) {
    const adminDashboard = new AdminDashboard();
}
```

## React/Vue Integration Examples

### React Hook for Real-time Features

```javascript
// useRealtimeCart.js
import { useState, useEffect } from "react";

export const useRealtimeCart = (cartId) => {
    const [cart, setCart] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Listen for cart updates
        const channel = window.Echo.channel(`cart.${cartId}`);

        channel.listen(".cart.updated", (event) => {
            setCart(event.cart_data);
            setLoading(false);
        });

        // Cleanup
        return () => {
            channel.stopListening(".cart.updated");
        };
    }, [cartId]);

    return { cart, loading };
};

// Component usage
function CartComponent() {
    const cartId = sessionStorage.getItem("cart_id");
    const { cart, loading } = useRealtimeCart(cartId);

    if (loading) return <div>Loading cart...</div>;

    return (
        <div>
            <h2>Shopping Cart ({cart?.total_items || 0} items)</h2>
            {cart?.items?.map((item) => (
                <div key={item.product_id}>
                    {item.name} - Qty: {item.quantity} - ${item.total}
                </div>
            ))}
            <div>Total: ${cart?.total_amount || 0}</div>
        </div>
    );
}
```

### Vue Composable

```javascript
// useRealtime.js
import { ref, onMounted, onUnmounted } from "vue";

export function useRealtimeOrders(userId) {
    const orders = ref([]);
    const notifications = ref([]);

    onMounted(() => {
        const channel = window.Echo.private(`user.${userId}`);

        channel.listen(".order.status.updated", (event) => {
            // Update order in list
            const orderIndex = orders.value.findIndex(
                (o) => o.id === event.order_id
            );
            if (orderIndex !== -1) {
                orders.value[orderIndex].status = event.new_status;
            }

            // Add notification
            notifications.value.push({
                id: Date.now(),
                message: `Order #${event.order_id} status updated to ${event.new_status}`,
                type: "info",
                timestamp: new Date(),
            });
        });

        // Cleanup handled by onUnmounted
    });

    onUnmounted(() => {
        window.Echo.leave(`user.${userId}`);
    });

    return {
        orders,
        notifications,
    };
}
```

## Troubleshooting

### Common Issues

1. **Connection Failed**

    - Ensure Reverb server is running: `php artisan reverb:start`
    - Check WebSocket port (8080) is not blocked
    - Verify environment variables are correct

2. **Authentication Issues**

    - Ensure token is included in Echo configuration
    - Check token is valid and not expired
    - Verify channel authorization in `routes/channels.php`

3. **Events Not Received**
    - Check event is being broadcast: `Log::info('Broadcasting event', $event)`
    - Verify channel names match exactly
    - Ensure listeners use correct event names (with dot prefix: `.order.created`)

### Debug Mode

Enable debug mode to see connection details:

```javascript
const echo = new Echo({
    // ... other config
    enableLogging: true,
    logToConsole: true,
});
```

### Testing WebSocket Connection

```javascript
// Test connection
window.Echo.connector.socket.on("connect", () => {
    console.log("WebSocket connected successfully!");
});

window.Echo.connector.socket.on("disconnect", () => {
    console.log("WebSocket disconnected");
});

// Test channel subscription
const testChannel = window.Echo.channel("test-channel");
testChannel.listen(".test-event", (e) => {
    console.log("Test event received:", e);
});
```

This guide should help you integrate real-time features into any frontend framework. The key is proper WebSocket setup and event handling to create a responsive, modern e-commerce experience.
