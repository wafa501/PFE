import React, { useEffect, useState, useRef } from 'react';
import { FaBell, FaHeart, FaComment } from 'react-icons/fa';
import Pusher from 'pusher-js';
import './Notifications.css'; 

const Notifications = () => {
    const [notifications, setNotifications] = useState([]);
    const [showNotifications, setShowNotifications] = useState(false);
    const [unseenNotifications, setUnseenNotifications] = useState(0);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const pusher = new Pusher('f321f88b081f3fec90a1', {
            cluster: 'eu',
        });

        const channel = pusher.subscribe('notifications');

        channel.bind('App\\Events\\PostThresholdExceeded', function(data) {
            const notification = {
                type: data.postId.includes('like') ? 'like' : 'comment',
                message: data.postId,
                seen: false, 
            };
            console.log('New Notification:', notification);
            setNotifications(prevNotifications => [...prevNotifications, notification]);
            setUnseenNotifications(prevCount => prevCount + 1);
        });

        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setShowNotifications(false);
                console.log('Click outside detected, closing notifications');
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
            pusher.unsubscribe('notifications');
        };
    }, []);

    const handleClick = () => {
        console.log('Notification icon clicked'); 
        setShowNotifications(prevState => {
            const newState = !prevState;
            if (newState) {
                const updatedNotifications = notifications.map(notification => ({ ...notification, seen: true }));
                setNotifications(updatedNotifications);
                setUnseenNotifications(0);
            }
            console.log('Toggled showNotifications to:', newState);
            return newState;
        });
    };
    
    useEffect(() => {
        console.log('Current state of showNotifications:', showNotifications);
    }, [showNotifications]);
    

    const renderNotificationIcon = (notification) => {
        if (notification.type === 'like') {
            return <FaHeart color="red" />;
        } else if (notification.type === 'comment') {
            return <FaComment color="blue" />;
        } else {
            return <FaBell />;
        }
    };

    return (
        <div className="notification-container" ref={dropdownRef}>
            <div className="notification-icon" onClick={handleClick}>
                <FaBell size={24} />
                {unseenNotifications > 0 && (
                    <span className="notification-count">{unseenNotifications}</span>
                )}
            </div>
            {showNotifications && (
                <div 
                    className="notifications-dropdown" 
                    style={{ display: 'block', backgroundColor: 'white', border: '1px solid black', zIndex: 1000 }}
                >
                    <ul>
                        {notifications.length > 0 ? (
                            notifications.map((notification, index) => (
                                <li key={index} className="notification-item">
                                    {renderNotificationIcon(notification)}
                                    <span className="notification-message">{notification.message}</span>
                                </li>
                            ))
                        ) : (
                            <li className="notification-item">
                                <span>No new notifications</span>
                            </li>
                        )}
                    </ul>
                </div>
            )}

        </div>
    );
};

export default Notifications;
