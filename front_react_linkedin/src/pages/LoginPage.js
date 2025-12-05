import React from 'react';
import './css/LoginPage.css';

const LoginPage = ({ setIsAuthenticated }) => {
    const handleLogin = () => {
        window.location.href = 'http://localhost:8000/auth/linkedin';
    };

    return (
        <div className="login-page">
            <h1>Connectez-vous avec LinkedIn</h1>
            <button onClick={handleLogin}>Se connecter avec LinkedIn</button>
        </div>
    );
};

export default LoginPage;
