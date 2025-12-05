import React, { useEffect, useState } from 'react';
import axios from 'axios';
import DashboardLayoutFacebook from './DashboardLayoutFacebook';
import './css/FbStatistics.css';

const FacebookStatistics = () => {
    const [stats, setStats] = useState([]);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(true);
    const [view, setView] = useState('static'); 
    const [selectedPage, setSelectedPage] = useState(''); 

    useEffect(() => {
        const fetchStats = async () => {
            setLoading(true); 
            try {
                const response = await axios.get('http://localhost:8000/statsPage_database', { withCredentials: true });

                const pagesPromises = response.data.map(async (stat) => {
                    const pageId = stat.fb_id.split('_')[0];
                    try {
                        const res = await axios.get(`http://localhost:8000/pageName/${pageId}`);
                        return { ...stat, pageName: res.data.name };
                    } catch {
                        return { ...stat, pageName: 'Unknown Page' }; 
                    }
                });

                const statsWithPageNames = await Promise.all(pagesPromises);
                setStats(statsWithPageNames);
            } catch (error) {
                console.error(error.response?.data || error);
                setError('Erreur lors de la récupération des statistiques');
            } finally {
                setLoading(false);
            }
        };
    
        fetchStats();
    }, []);
    
    if (loading) return <DashboardLayoutFacebook><p>Loading...</p></DashboardLayoutFacebook>
    if (error) return <div className="error-message">Erreur : {error}</div>;

    return (
        <DashboardLayoutFacebook>
            <div className="view-toggle">
                <button 
                    className={`toggle-button ${view === 'static' ? 'active' : ''}`} 
                    onClick={() => setView('static')}
                >
                    Statique
                </button>
                <button 
                    className={`toggle-button ${view === 'graph' ? 'active' : ''}`} 
                    onClick={() => setView('graph')}
                >
                    Graphique
                </button>
            </div>

            <div className="page-selector">
                <label htmlFor="page-select">Sélectionner une page :</label>
                <select
                    id="page-select"
                    value={selectedPage}
                    onChange={(e) => setSelectedPage(e.target.value)} 
                >
                    <option value="">--Choisir une page--</option>
                    {stats.map((stat) => (
                        <option key={stat.id} value={stat.pageName}>
                            {stat.pageName} 
                        </option>
                    ))}
                </select>
            </div>

            <div className={`statistics-container ${view}`}>
                {view === 'static' ? (
                    stats
                        .filter((stat) => selectedPage === '' || stat.pageName === selectedPage) 
                        .map((stat) => (
                            <div key={stat.id} className="stat-card fade-in">
                                <h3 className="stat-title">{stat.pageName}</h3> 
                            
                                <p className="stat-description">{stat.description}</p>
                                <span className="stat-value">{stat.value}</span>
                                <span className="stat-period">{stat.period}</span>
                            </div>
                        ))
                ) : (
                    <div className="graph-placeholder">Graphiques ici</div>
                )}
            </div>
        </DashboardLayoutFacebook>
    );
};

export default FacebookStatistics;
