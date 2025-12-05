import React from 'react';
import { useNavigate } from 'react-router-dom';
import './logoutButton.css'; 

function LogoutFacebookButton() {
    const navigate = useNavigate();

    const handleLogout = async () => {
        try {
            const response = await fetch('http://localhost:8000/deconnexion-facebook', {
                method: 'GET', 
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include', 
            });

            if (response.ok) {
                console.log('ok');
                navigate('/choice'); 
            } else {
                console.error('Logout failed');
            }
        } catch (error) {
            console.error('An error occurred during logout:', error);
        }
    };

    return (
        <button className="logout-button5" onClick={handleLogout}>
            Déconnexion
        </button>
    );
}

export default LogoutFacebookButton;
