import React from 'react';
import { useNavigate } from 'react-router-dom';
import "../pages/css/button.css"

const LogoutButton = () => {
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      const response = await fetch('http://localhost:8000/api/deconnexion', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        navigate('/login'); 
      } else {
        console.error('Logout failed');
      }
    } catch (error) {
      console.error('An error occurred during logout:', error);
    }
  };

  return (
    <button onClick={handleLogout} className='btn1'>
      Log Out
    </button>
  );
};

export default LogoutButton;
