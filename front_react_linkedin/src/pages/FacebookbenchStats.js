import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './css/ReactionsFacebookPage.css';
import DashboardLayoutFacebook from './DashboardLayoutFacebook';
import { Bar } from 'react-chartjs-2'; 
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const FacebookStatistics = () => {
    // eslint-disable-next-line no-unused-vars
    const [reactions, setReactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pageStats, setPageStats] = useState({}); 
    const [selectedPageIds, setSelectedPageIds] = useState([]); 
    const [pageNames, setPageNames] = useState({}); 

    useEffect(() => {
        const fetchReactions = async () => {
            try {
                const response = await axios.get('http://localhost:8000/Fetch_AllReactions', {
                    withCredentials: true,
                });
                setReactions(response.data);

                const stats = {};
                response.data.forEach(reaction => {
                    const pageId = reaction.post_id.split('_')[0]; 

                    if (!stats[pageId]) {
                        stats[pageId] = {
                            totalPosts: 0,
                            totalReactions: 0,
                            totalComments: 0,
                        };
                    }

                    stats[pageId].totalPosts += 1; 
                    stats[pageId].totalReactions += reaction.total_reactions || 0;
                    stats[pageId].totalComments += reaction.comments_count || 0;
                });

                setPageStats(stats);
                fetchPageNames(stats); 
            } catch (error) {
                setError('Erreur lors de la récupération des données');
                console.error(error);
            } finally {
                setLoading(false);
            }
        };

        const fetchPageNames = async (stats) => {
            const ids = Object.keys(stats);
            const names = {};

            await Promise.all(ids.map(async (id) => {
                try {
                    const response = await axios.get(`http://localhost:8000/getOtherPageName/${id}`, {
                        withCredentials: true,
                    });
                    names[id] = response.data.name; 
                } catch (error) {
                    console.error(`Error fetching name for page ID ${id}:`, error);
                }
            }));

            setPageNames(names);
        };

        fetchReactions();
    }, []);

    const handlePageChange = (event) => {
        const pageId = event.target.value;
        setSelectedPageIds((prevSelected) =>
            prevSelected.includes(pageId)
                ? prevSelected.filter(id => id !== pageId) 
                : [...prevSelected, pageId]
        );
    };

    const generateChartData = () => {
        const labels = selectedPageIds.map(pageId => pageNames[pageId] || pageId);
        const postsData = selectedPageIds.map(pageId => pageStats[pageId]?.totalPosts || 0);
        const reactionsData = selectedPageIds.map(pageId => pageStats[pageId]?.totalReactions || 0);
        const commentsData = selectedPageIds.map(pageId => pageStats[pageId]?.totalComments || 0);

        return {
            labels,
            datasets: [
                {
                    label: 'Nombre de Posts 📄',
                    data: postsData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Réactions ❤️',
                    data: reactionsData,
                    backgroundColor: 'rgba(153, 102, 255, 0.6)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Commentaires 💬',
                    data: commentsData,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                },
            ],
        };
    };

    if (loading) {
        return <div>Chargement des statistiques...</div>;
    }

    if (error) {
        return <div>{error}</div>;
    }

    return (
        <DashboardLayoutFacebook>
            <div>
                <h1>Statistiques des Réactions Facebook</h1>

                <div>
                    <h3>Sélectionner des Pages</h3>
                    <div className="checkbox-container">
    {Object.keys(pageStats).map(pageId => (
        <div key={pageId} className="checkbox-group">
            <input
                type="checkbox"
                value={pageId}
                checked={selectedPageIds.includes(pageId)}
                onChange={handlePageChange}
            />
            <label>{pageNames[pageId] || "Nom en cours de chargement..."}</label>
        </div>
    ))}
</div>
                </div>

                {selectedPageIds.length > 0 ? (
                    <div>

                        {/* Chart */}
                        <Bar
    data={generateChartData()}
    options={{
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Comparaison des Réactions par Page',
            },
        },
        scales: {
            y: {
                beginAtZero: true, 
                min: 0, 
                ticks: {
                    stepSize: 1, 
                },
            },
        },
    }}
/>

                    </div>
                ) : (
                    <p>Aucune page sélectionnée.</p>
                )}
            </div>
        </DashboardLayoutFacebook>
    );
};

export default FacebookStatistics;
