import React, { useEffect, useState } from 'react';
import DashboardLayoutFacebook from './DashboardLayoutFacebook';
import { Bar } from 'react-chartjs-2';
import axios from 'axios';
import './css/DashFb.css';

// Importations nécessaires pour Chart.js
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  PointElement,
  LineElement
} from 'chart.js';

// Enregistrer les composants Chart.js
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  PointElement,
  LineElement
);

const DashboardFacebook = () => {
    const [stats, setStats] = useState([]);
    const [error, setError] = useState(null);
    const [viewType, setViewType] = useState('cards'); 
    const [selectedPageId, setSelectedPageId] = useState('');
    const [pages, setPages] = useState([]); 
    const [loading, setLoading] = useState(true);
    const [chartType, setChartType] = useState('bar'); // Ajout pour changer le type de graphique
    const [sortOrder, setSortOrder] = useState('desc'); // Ajout pour trier les données

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const response = await axios.get('http://localhost:8000/statsPage_database', { withCredentials: true });
                setStats(response.data);

                const pageIds = [...new Set(response.data.map(stat => stat.fb_id.split('/')[0]))];
                const pagePromises = pageIds.map(async (pageId) => {
                    try {
                        const res = await axios.get(`http://localhost:8000/pageName/${pageId}`); 
                        return { id: pageId, name: res.data.name };
                    } catch {
                        return { id: pageId, name: 'Page inconnue' }; 
                    }
                });

                const pagesData = await Promise.all(pagePromises);
                setPages(pagesData);
            } catch (error) {
                setError(error.response?.data?.message || 'Erreur lors de la récupération des statistiques');
            } finally {
                setLoading(false);
            }
        };

        fetchStats();
    }, []);

    // Grouper les stats quand aucune page n'est sélectionnée
    const groupStats = (stats) => {
        const grouped = {};

        stats.forEach(stat => {
            const key = stat.title || "Titre non disponible";

            if (!grouped[key]) {
                grouped[key] = {
                    ...stat,
                    value: Number(stat.value)
                };
            } else {
                grouped[key].value += Number(stat.value); 
            }
        });

        return Object.values(grouped);
    };

    let filteredStats = selectedPageId 
        ? stats.filter(stat => stat.fb_id.split('/')[0] === selectedPageId)
        : groupStats(stats);

    // Trier les données selon l'ordre choisi
    filteredStats = [...filteredStats].sort((a, b) => {
        const valA = Number(a.value);
        const valB = Number(b.value);
        return sortOrder === 'desc' ? valB - valA : valA - valB;
    });

    // Préparer les données pour le graphique
    const labels = filteredStats.map(stat => {
        const title = stat.title || 'Titre non disponible';
        // Tronquer les titres trop longs pour l'affichage
        return title.length > 20 ? title.substring(0, 20) + '...' : title;
    });
    
    const values = filteredStats.map(stat => Number(stat.value));

    // Fonction pour générer un dégradé de couleurs
    const generateGradientColors = (count) => {
        const colors = [];
        const hueStep = 360 / count;
        
        for (let i = 0; i < count; i++) {
            const hue = (i * hueStep) % 360;
            colors.push(`hsla(${hue}, 70%, 60%, 0.7)`);
        }
        return colors;
    };

    // Fonction pour générer les bordures
    const generateBorderColors = (count) => {
        const colors = [];
        const hueStep = 360 / count;
        
        for (let i = 0; i < count; i++) {
            const hue = (i * hueStep) % 360;
            colors.push(`hsla(${hue}, 70%, 50%, 1)`);
        }
        return colors;
    };

    const backgroundColors = generateGradientColors(values.length);
    const borderColors = generateBorderColors(values.length);

    const chartData = {
        labels: labels,
        datasets: [
            {
                label: 'Valeurs',
                data: values,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 2,
                borderRadius: 5,
                borderSkipped: false,
                hoverBackgroundColor: backgroundColors.map(color => color.replace('0.7', '0.9')),
                hoverBorderWidth: 3,
            },
        ],
    };

    // Options avancées pour le graphique
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        aspectRatio: window.innerWidth < 768 ? 1 : 2,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: {
                        size: 14,
                        family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                    },
                    padding: 20,
                    usePointStyle: true,
                }
            },
            title: {
                display: true,
                text: selectedPageId 
                    ? `Statistiques de la page: ${pages.find(p => p.id === selectedPageId)?.name || 'Page sélectionnée'}`
                    : 'Statistiques globales de toutes les pages',
                font: {
                    size: 18,
                    weight: 'bold'
                },
                padding: {
                    top: 20,
                    bottom: 30
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                titleFont: {
                    size: 14
                },
                bodyFont: {
                    size: 13
                },
                padding: 12,
                cornerRadius: 6,
                callbacks: {
                    label: (context) => {
                        const label = context.dataset.label || '';
                        const value = context.parsed.y;
                        const index = context.dataIndex;
                        const originalTitle = filteredStats[index].title || 'Titre non disponible';
                        
                        return [
                            `${label}: ${value.toLocaleString()}`,
                            `Titre: ${originalTitle}`,
                            filteredStats[index].description ? `Description: ${filteredStats[index].description}` : ''
                        ];
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)'
                },
                ticks: {
                    font: {
                        size: 12
                    },
                    callback: function(value) {
                        if (value >= 1000000) {
                            return (value / 1000000).toFixed(1) + 'M';
                        }
                        if (value >= 1000) {
                            return (value / 1000).toFixed(1) + 'k';
                        }
                        return value;
                    }
                },
                title: {
                    display: true,
                    text: 'Valeurs',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        },
        onHover: (event, chartElement) => {
            event.native.target.style.cursor = chartElement[0] ? 'pointer' : 'default';
        },
        onClick: (event, elements) => {
            if (elements.length > 0) {
                const index = elements[0].index;
                const stat = filteredStats[index];
                alert(`Statistique détaillée:\n\nTitre: ${stat.title}\nValeur: ${stat.value}\nDescription: ${stat.description || 'Aucune description'}`);
            }
        }
    };

    if (loading) return (
        <DashboardLayoutFacebook>
            <div className="fb-loading">
                <div className="fb-spinner"></div>
                <p>Chargement des statistiques...</p>
            </div>
        </DashboardLayoutFacebook>
    );

    if (error) return (
        <DashboardLayoutFacebook>
            <div className="fb-error-message">
                <span className="fb-error-icon">⚠️</span>
                <p>Erreur : {error}</p>
                <button onClick={() => window.location.reload()} className="fb-retry-button">
                    Réessayer
                </button>
            </div>
        </DashboardLayoutFacebook>
    );

    return (
        <DashboardLayoutFacebook>
            <div className="fb-controls-container">
                <div className="fb-page-selection">
                    <label htmlFor="page-select" className="fb-label-marge">Sélectionner une page</label>
                    <select 
                        id="page-select" 
                        className="fb-custom-select" 
                        onChange={(e) => setSelectedPageId(e.target.value)} 
                        value={selectedPageId}
                    >
                        <option value="">Toutes les pages</option>
                        {pages.length > 0 ? (
                            pages.map(page => (
                                <option key={page.id} value={page.id}>
                                    {page.name}
                                </option>
                            ))
                        ) : (
                            <option value="">Aucune page disponible</option>
                        )}
                    </select>
                </div>

                <div className="fb-chart-controls">
                    <div className="fb-control-group">
                        <label className="fb-control-label">Type de graphique:</label>
                        <select 
                            className="fb-chart-type-select"
                            value={chartType}
                            onChange={(e) => setChartType(e.target.value)}
                        >
                            <option value="bar">Barres</option>
                            <option value="line">Ligne</option>
                        </select>
                    </div>

                    <div className="fb-control-group">
                        <label className="fb-control-label">Trier par:</label>
                        <select 
                            className="fb-sort-select"
                            value={sortOrder}
                            onChange={(e) => setSortOrder(e.target.value)}
                        >
                            <option value="desc">Valeurs décroissantes</option>
                            <option value="asc">Valeurs croissantes</option>
                            <option value="alpha">Ordre alphabétique</option>
                        </select>
                    </div>
                </div>
            </div>

            <div className="fb-view-toggle">
                <button 
                    onClick={() => setViewType('cards')} 
                    className={`fb-toggle-button ${viewType === 'cards' ? 'active' : ''}`}
                >
                    📊 Affichage Statistiques
                </button>
                <button 
                    onClick={() => setViewType('charts')} 
                    className={`fb-toggle-button ${viewType === 'charts' ? 'active' : ''}`}
                >
                    📈 Affichage Graphiques
                </button>
            </div>

            {viewType === 'cards' ? (
                <div className="fb-stats-container">
                    {filteredStats.map((stat) => (
                        <div key={stat.id} className="fb-stat-card fb-fade-in">
                            <h3 className="fb-stat-title">{stat.title || 'Titre non disponible'}</h3>
                            <p className="fb-stat-description">{stat.description}</p>
                            <div className="fb-stat-value-container">
                                <span className="fb-stat-value">{Number(stat.value).toLocaleString()}</span>
                            </div>
                            <span className="fb-stat-period">{stat.period}</span>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="fb-chart-wrapper">
                    <div className="fb-chart-container">
                        {chartType === 'bar' ? (
                            <Bar data={chartData} options={chartOptions} />
                        ) : (
                            <Bar 
                                data={{
                                    ...chartData,
                                    datasets: [{
                                        ...chartData.datasets[0],
                                        type: 'line',
                                        borderWidth: 3,
                                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                                        pointBackgroundColor: borderColors,
                                        pointBorderColor: '#fff',
                                        pointBorderWidth: 2,
                                        pointRadius: 6,
                                        pointHoverRadius: 8,
                                        tension: 0.3
                                    }]
                                }} 
                                options={chartOptions} 
                            />
                        )}
                    </div>
                    
                    <div className="fb-chart-summary">
                        <h3>Résumé des données</h3>
                        <div className="fb-summary-stats">
                            <div className="fb-summary-stat">
                                <span className="fb-summary-label">Total:</span>
                                <span className="fb-summary-value">
                                    {values.reduce((a, b) => a + b, 0).toLocaleString()}
                                </span>
                            </div>
                            <div className="fb-summary-stat">
                                <span className="fb-summary-label">Moyenne:</span>
                                <span className="fb-summary-value">
                                    {(values.reduce((a, b) => a + b, 0) / values.length || 0).toFixed(2)}
                                </span>
                            </div>
                            <div className="fb-summary-stat">
                                <span className="fb-summary-label">Maximum:</span>
                                <span className="fb-summary-value">
                                    {Math.max(...values).toLocaleString()}
                                </span>
                            </div>
                            <div className="fb-summary-stat">
                                <span className="fb-summary-label">Minimum:</span>
                                <span className="fb-summary-value">
                                    {Math.min(...values).toLocaleString()}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </DashboardLayoutFacebook>
    );
};

export default DashboardFacebook;