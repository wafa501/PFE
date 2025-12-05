import React, { useEffect, useState } from 'react';
import './css/ProfilePage.css'; 
import DashboardLayout from './DashboardLayout';

const ProfilePage = () => {
  const [profile, setProfile] = useState({
    name: '',
    picture: '',
    given_name: '',
    family_name: '',
    email: '',
    locale_country: '',
    locale_language: '',
    localizedHeadline: ''
  });

  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const createParticles = () => {
      const particlesContainer = document.querySelector('.linkedin-particles');
      if (particlesContainer) {
        for (let i = 0; i < 15; i++) {
          const particle = document.createElement('div');
          particle.className = 'particle';
          particle.style.cssText = `
            top: ${Math.random() * 100}%;
            left: ${Math.random() * 100}%;
            width: ${Math.random() * 10 + 5}px;
            height: ${Math.random() * 10 + 5}px;
            animation-delay: ${Math.random() * -20}s;
          `;
          particlesContainer.appendChild(particle);
        }
      }
    };

    createParticles();
  }, []);

  useEffect(() => {
    const fetchProfile = async () => {
      try { 
        setLoading(true);
        const response = await fetch("http://localhost:8000/api/profile", {
          method: "GET",
          headers: {
            "Content-Type": "application/json",
          },
          credentials: "include",
        });

        const data = await response.json();
        setProfile({
  name: data.user?.name || 'Utilisateur LinkedIn',
  picture: data.user?.profile_picture || 'https://via.placeholder.com/200',
  given_name: data.user?.given_name || 'Prénom',
  family_name: data.user?.family_name || 'Nom',
  email: data.user?.email || 'email@example.com',
  locale_country: data.user?.locale?.country || 'Pays',
  locale_language: data.user?.locale?.language || 'Langue',
  localizedHeadline: data.user?.headline || 'Professionnel LinkedIn'
});

      } catch (error) {
        console.error('Error fetching profile data:', error);
      } finally {
        setLoading(false);
      }
    };
    
    fetchProfile();
  }, []);

  if (loading) {
    return (
      <DashboardLayout>
        <div className="profile-container">
          <div className="linkedin-particles"></div>
          <div className="linkedin-waves">
            <div className="wave"></div>
            <div className="wave"></div>
            <div className="wave"></div>
          </div>
          <div className="network-connections">
            <div className="connection"></div>
            <div className="connection"></div>
            <div className="connection"></div>
            <div className="connection"></div>
            <div className="connection-dot"></div>
            <div className="connection-dot"></div>
            <div className="connection-dot"></div>
            <div className="connection-dot"></div>
          </div>
          <div className="profile-content">
            <div className="profile-header">
              <div className="profile-picture"></div>
              <h1>Chargement du profil...</h1>
            </div>
          </div>
        </div>
      </DashboardLayout>
    );
  }

  return (
    <DashboardLayout>
      <div className="profile-container">
        <div className="linkedin-particles"></div>
        <div className="linkedin-waves">
          <div className="wave"></div>
          <div className="wave"></div>
          <div className="wave"></div>
        </div>
        <div className="network-connections">
          <div className="connection"></div>
          <div className="connection"></div>
          <div className="connection"></div>
          <div className="connection"></div>
          <div className="connection-dot"></div>
          <div className="connection-dot"></div>
          <div className="connection-dot"></div>
          <div className="connection-dot"></div>
        </div>
        
        <div className="profile-content">
          <div className="profile-header">
            <img 
              src={profile.picture} 
              alt="Profile"  
              className="profile-picture"
              onError={(e) => {
                e.target.src = 'https://via.placeholder.com/200';
              }}
            />
            <h1>{profile.name}</h1>
            
           
          </div>

          <div className="profile-details">
            <div className="detail-card">
              <h3>Informations Personnelles</h3>
              <div className="detail-item">
                <span className="detail-label">Prénom:</span>
                <span className="detail-value">{profile.given_name}</span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Nom:</span>
                <span className="detail-value">{profile.family_name}</span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Email:</span>
                <span className="detail-value">{profile.email}</span>
              </div>
            </div>
            
            <div className="detail-card">
              <h3>Localisation</h3>
              <div className="detail-item">
                <span className="detail-label">Pays:</span>
                <span className="detail-value">{profile.locale_country}</span>
              </div>
              <div className="detail-item">
                <span className="detail-label">Langue:</span>
                <span className="detail-value">{profile.locale_language}</span>
              </div>
            
            </div>
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default ProfilePage;