import React, { useEffect, useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import LogoutFacebookButton from '../components/LogoutFacebookButton';
import './css/DashFb.css';

const DashboardLayoutFacebook = ({ children }) => {
    const [profile, setProfile] = useState({ name: '', email: '' });
    const [imageUrl, setImageUrl] = useState('https://via.placeholder.com/120'); 
    const [fbId, setFbId] = useState('');
        const [isMobile, setIsMobile] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);


    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

        useEffect(() => {
        const checkMobile = () => {
            setIsMobile(window.innerWidth <= 480);
        };
        
        checkMobile();
        window.addEventListener('resize', checkMobile);
        
        return () => window.removeEventListener('resize', checkMobile);
    }, []);
    useEffect(() => {
        const fetchProfileData = async () => {
            try {
                const response = await fetch("http://localhost:8000/facebook/profile", {
                    method: "GET",
                    headers: { "Content-Type": "application/json" },
                    credentials: "include",
                });

                if (!response.ok) throw new Error('Network response was not ok');

                const data = await response.json();
const userData = data.user || {};
setProfile(userData);

if (userData.profile_picture) {
    setImageUrl(userData.profile_picture);
}

setLoading(false);

            } catch (error) {
                console.error('Error fetching profile data:', error);
                setError('Failed to load data.');
                setLoading(false);
                navigate('/choice'); 
            }
        };

        fetchProfileData();
    }, [navigate]);

    useEffect(() => {
        const fetchIdData = async () => {
            if (profile.email) {
                try { 
                    const response = await axios.get(`http://localhost:8000/get-user-id-by-email/${profile.email}`);
                    if (response.data && response.data.facebook_id) {
                        setFbId(response.data.facebook_id); 
                    } else {
                        console.error('No facebook_id found in the response');
                    }
                } catch (error) {
                    console.error('Error fetching id data:', error);
                }
            }
        };
    
        fetchIdData();
    }, [profile.email]);


    const handleProfileClick = () => {
        navigate('/FacebookProfile');
    };

    if (error) return <p className="fb-error-message">Error: {error}</p>;

     return (
        <div className="fb-dashboard">
            {/* Bouton hamburger pour mobile */}
            {isMobile && (
                <button 
                    className="fb-mobile-menu-toggle"
                    onClick={() => setMenuOpen(!menuOpen)}
                >
                    {menuOpen ? '✕' : '☰'}
                </button>
            )}
            
            <div className="fb-page-container">
                <aside className={`fb-sidebar ${isMobile && !menuOpen ? 'fb-sidebar-hidden' : ''}`}>
                    <div className="fb-profile-section">
                        <div className="fb-image-design" onClick={handleProfileClick}>
                            <img src={imageUrl} alt="Profile" />
                        </div>
                        <h2 className="fb-profile-name">{profile.name || 'Loading...'}</h2>
                    </div>
                    <nav className="fb-menu">
                        <li><Link to="/DashboardFacebook" onClick={() => isMobile && setMenuOpen(false)}>Home</Link></li>
                        <li><Link to="/FacebookPostsDetails" onClick={() => isMobile && setMenuOpen(false)}>My Pages posts</Link></li>
                        <li><Link to="/FacebookPageDetails" onClick={() => isMobile && setMenuOpen(false)}>Pages details</Link></li>
                        <li><Link to="/FacebookbenchStats" onClick={() => isMobile && setMenuOpen(false)}>Benchmarking</Link></li>
                        <li><Link to="/ReactionsFacebookPage" onClick={() => isMobile && setMenuOpen(false)}>Statistics reactions</Link></li>
                        <li><LogoutFacebookButton /></li>
                    </nav>
                </aside>
                <main className="fb-content">
                    {children}
                </main>
            </div>
        </div>
    );
};


export default DashboardLayoutFacebook;