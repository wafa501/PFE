import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './css/Dashboard.css';
import { Link } from 'react-router-dom';
import LogoutButton from '../components/LogoutButton';
import axios from 'axios';

const DashboardLayout = ({ children }) => {
    const [profile, setProfile] = useState({ name: '', picture: '', role: '', blocked: false });
    const navigate = useNavigate();
    const [role, setRole] = useState('user');

    useEffect(() => {
        const fetchProfile = async () => {
            try { 
                const response = await axios.get("http://localhost:8000/api/profile", { withCredentials: true });
                const user = response.data.user;

                setProfile({
                    name: user?.name || 'Utilisateur LinkedIn',
                    picture: user?.profile_picture || 'https://via.placeholder.com/200',
                    role: user?.role || 'user',
                    blocked: user?.blocked || false, // <-- récupérer blocked
                    given_name: user?.given_name || 'Prénom',
                    family_name: user?.family_name || 'Nom',
                    email: user?.email || 'email@example.com',
                    locale_country: user?.locale?.country || 'Pays',
                    locale_language: user?.locale?.language || 'Langue',
                    localizedHeadline: user?.headline || 'Professionnel LinkedIn'
                });

                // Si l'utilisateur est bloqué, le rediriger vers login
                if (user?.blocked) {
                    alert('Votre compte est bloqué. Vous ne pouvez pas accéder à l’application.');
                    navigate('/login', { replace: true });
                }

            } catch (error) {
                console.error('Error fetching profile data:', error);
            }
        };
        
        fetchProfile();
    }, [navigate]);

    useEffect(() => {
        const fetchRole = async () => {
            const email = profile.email;
            if (!email) return;
            try {
                const res = await axios.get(`http://localhost:8000/api/user-role?email=${email}`, { withCredentials: true });
                setRole(res.data.role);
            } catch (err) {
                console.error(err);
            }
        };
        fetchRole();
    }, [profile.email]);

    const handleProfileClick = () => navigate('/profile');

    return (
        <div className="dashboard-container">
            <div className="page-container">
                <aside className="sidebar">
                    <div className="profile-section">
                        <div className="image-container" onClick={handleProfileClick}>
                            <img src={profile.picture} alt="Profile" />
                        </div>
                        <h2 className="profile-name">{profile.name}</h2>
                    </div>
                    <nav className="menu">
                        <h3 className="menu-title">Menu</h3>
                        <p><Link to="/dashboard">Home</Link></p>
                        <p><Link to="/AddDelListOrgLinkedin">Add concurrent</Link></p>
                        <p><Link to="/OrganizationSelectionPage">Organization</Link></p>
                        <p><Link to="/MyPostsPage/18931">SopraHr posts</Link></p>
                        <p><Link to="/posts">Organizations posts</Link></p>
                        <p><Link to="/statistics">Statistics</Link></p>

                        {role === 'admin' && <p><Link to="/GestionUsers">User Management</Link></p>}

                        <p><Link to="/prediction">Prédiction</Link></p>
                        <p><LogoutButton/></p>
                    </nav>
                </aside>
                <main className="content-area">
                    {children}
                </main>
            </div>
        </div>
    );
};

export default DashboardLayout;
