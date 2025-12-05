import React, { useState, useEffect } from 'react';
import { Line, Bar, Pie } from 'react-chartjs-2';
import 'chart.js/auto';
import './css/MyPosts.css';
import axios from 'axios';
import DashboardLayout from './DashboardLayout';

const URN_TO_NAME = {
    'urn:li:organization:18931': 'soprahr',
    'urn:li:organization:1463': 'adp',
    'urn:li:organization:38256': 'Vermeg',
    'urn:li:organization:71370828': 'teamlink',
};

const authorColors = {
    'soprahr': '#d40000', 
    'adp': '#FF7F00', 
    'Vermeg': '#0080FF', 
    'teamlink':'#0080F2',
};

const monthNames = [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
];

const organizationMap = {
    '18931': 'soprahr',
    '1463': 'adp',
    '38256': 'Vermeg',
    '71370828': 'teamlink',
};

const StatisticsPage = () => {
    const [data, setData] = useState([]);
    const [selectedOrganization, setSelectedOrganization] = useState('18931');
    const [selectedYear, setSelectedYear] = useState(null);
    const [selectedMonth, setSelectedMonth] = useState('1');
    const [comparisonMonth, setComparisonMonth] = useState('1');
    const [compareWithSopra, setCompareWithSopra] = useState('18931'); // Organisation pour comparer avec Sopra
    const [error, setError] = useState(null);

    // Fetch API
    const fetchData = async () => {
        try {
            const response = await axios.get('http://localhost:8000/api/MyStats', { withCredentials: true });
            setData(response.data);
        } catch (err) {
            console.error('Erreur API:', err);
            setError(err.message);
        }
    };

    useEffect(() => {
        fetchData();
    }, []);

    // Initialisation de l'année par défaut
    useEffect(() => {
        if (data.length > 0) {
            const defaultYear = data.find(item => item.organization.toString() === selectedOrganization)?.year;
            setSelectedYear(defaultYear);
        }
    }, [data, selectedOrganization]);

    // Filtrage pour la partie 1
    const filteredData = data.find(
        (item) => item.year === selectedYear && item.organization.toString() === selectedOrganization
    );

    // Filtrage pour la partie 2 : comparaison avec Sopra HR
    const sopraData = data.find(item => item.organization.toString() === '18931' && item.year === selectedYear);
    const compareData = data.find(item => item.organization.toString() === compareWithSopra && item.year === selectedYear);

    // Données Line / Bar / Pie pour Partie 1
    const lineDataPart1 = filteredData ? {
        labels: Object.keys(filteredData.monthly_stats).map(m => monthNames[m - 1]),
        datasets: [
            { label: 'Unique Impressions', data: Object.values(filteredData.monthly_stats).map(s => s.uniqueImpressionsCount), borderColor: 'rgba(75,192,192,1)', fill: false },
            { label: 'Likes', data: Object.values(filteredData.monthly_stats).map(s => s.likeCount), borderColor: 'rgba(0,69,245,0.8)', fill: false },
            { label: 'Comments', data: Object.values(filteredData.monthly_stats).map(s => s.commentCount), borderColor: 'rgba(20,200,66,0.8)', fill: false },
            { label: 'Clicks', data: Object.values(filteredData.monthly_stats).map(s => s.clickCount), borderColor: 'rgba(232,9,119,0.8)', fill: false },
        ]
    } : null;

    const barDataPart1 = filteredData ? {
        labels: ['Likes','Impressions','Shares','Engagement','Clics','Comment Mentions','Share Mentions','Comments'],
        datasets: [
            {
                label: monthNames[selectedMonth - 1],
                data: Object.values(filteredData.monthly_stats[selectedMonth] || {}).length
                    ? [
                        filteredData.monthly_stats[selectedMonth]?.likeCount || 0,
                        filteredData.monthly_stats[selectedMonth]?.uniqueImpressionsCount || 0,
                        filteredData.monthly_stats[selectedMonth]?.shareCount || 0,
                        filteredData.monthly_stats[selectedMonth]?.engagement || 0,
                        filteredData.monthly_stats[selectedMonth]?.clickCount || 0,
                        filteredData.monthly_stats[selectedMonth]?.commentMentionsCount || 0,
                        filteredData.monthly_stats[selectedMonth]?.shareMentionsCount || 0,
                        filteredData.monthly_stats[selectedMonth]?.commentCount || 0,
                    ] : [],
                backgroundColor: 'rgba(54,162,235,0.6)',
            },
            {
                label: monthNames[comparisonMonth - 1],
                data: Object.values(filteredData.monthly_stats[comparisonMonth] || {}).length
                    ? [
                        filteredData.monthly_stats[comparisonMonth]?.likeCount || 0,
                        filteredData.monthly_stats[comparisonMonth]?.uniqueImpressionsCount || 0,
                        filteredData.monthly_stats[comparisonMonth]?.shareCount || 0,
                        filteredData.monthly_stats[comparisonMonth]?.engagement || 0,
                        filteredData.monthly_stats[comparisonMonth]?.clickCount || 0,
                        filteredData.monthly_stats[comparisonMonth]?.commentMentionsCount || 0,
                        filteredData.monthly_stats[comparisonMonth]?.shareMentionsCount || 0,
                        filteredData.monthly_stats[comparisonMonth]?.commentCount || 0,
                    ] : [],
                backgroundColor: 'rgba(255,99,132,0.6)',
            },
        ]
    } : null;

    // Partie 2 : Comparaison avec Sopra HR pour le mois sélectionné
    const barDataPart2 = (sopraData && compareData) ? {
        labels: ['Likes','Impressions','Shares','Engagement','Clics','Comment Mentions','Share Mentions','Comments'],
        datasets: [
            {
                label: 'Sopra HR',
                data: Object.values(sopraData.monthly_stats[selectedMonth] || []).length
                    ? [
                        sopraData.monthly_stats[selectedMonth]?.likeCount || 0,
                        sopraData.monthly_stats[selectedMonth]?.uniqueImpressionsCount || 0,
                        sopraData.monthly_stats[selectedMonth]?.shareCount || 0,
                        sopraData.monthly_stats[selectedMonth]?.engagement || 0,
                        sopraData.monthly_stats[selectedMonth]?.clickCount || 0,
                        sopraData.monthly_stats[selectedMonth]?.commentMentionsCount || 0,
                        sopraData.monthly_stats[selectedMonth]?.shareMentionsCount || 0,
                        sopraData.monthly_stats[selectedMonth]?.commentCount || 0,
                    ] : [],
                backgroundColor: 'rgba(75,192,192,0.6)',
            },
            {
                label: organizationMap[compareWithSopra],
                data: Object.values(compareData.monthly_stats[selectedMonth] || []).length
                    ? [
                        compareData.monthly_stats[selectedMonth]?.likeCount || 0,
                        compareData.monthly_stats[selectedMonth]?.uniqueImpressionsCount || 0,
                        compareData.monthly_stats[selectedMonth]?.shareCount || 0,
                        compareData.monthly_stats[selectedMonth]?.engagement || 0,
                        compareData.monthly_stats[selectedMonth]?.clickCount || 0,
                        compareData.monthly_stats[selectedMonth]?.commentMentionsCount || 0,
                        compareData.monthly_stats[selectedMonth]?.shareMentionsCount || 0,
                        compareData.monthly_stats[selectedMonth]?.commentCount || 0,
                    ] : [],
                backgroundColor: 'rgba(255,99,132,0.6)',
            },
        ]
    } : null;

    if (!filteredData) return <p>Aucune donnée disponible pour cette année et cette organisation.</p>;

    return (
        <DashboardLayout>
            <div className="statistics-page">
                <h2>Statistics Dashboard</h2>

                {/* Partie 1 : Comparaison mensuelle par société */}
               <div className="chart-section">
    <h3>Comparaison mensuelle par société</h3>

    {/* Sélecteurs */}
    <div className="selectors-container" style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '20px' }}>
        {/* Gauche : Société + Année */}
        <div className="left-selectors" style={{ display: 'flex', gap: '10px' }}>
            <div className="selector">
                <label>Organisation :</label>
                <select value={selectedOrganization} onChange={e => setSelectedOrganization(e.target.value)}>
                    {Object.keys(organizationMap).map(id => (
                        <option key={id} value={id}>{organizationMap[id]}</option>
                    ))}
                </select>
            </div>
            <div className="selector">
                <label>Année :</label>
                <select value={selectedYear} onChange={e => setSelectedYear(parseInt(e.target.value))}>
                    {data.filter(d => d.organization.toString() === selectedOrganization).map(d => (
                        <option key={d.year} value={d.year}>{d.year}</option>
                    ))}
                </select>
            </div>
        </div>

        {/* Droite : Mois + Mois comparaison */}
        <div className="right-selectors" style={{ display: 'flex', gap: '10px' }}>
            <div className="selector">
                <label>Mois :</label>
                <select value={selectedMonth} onChange={e => setSelectedMonth(e.target.value)}>
                    {Object.keys(filteredData.monthly_stats).map(m => (
                        <option key={m} value={m}>{monthNames[m - 1]}</option>
                    ))}
                </select>
            </div>
            <div className="selector">
                <label>Mois comparaison :</label>
                <select value={comparisonMonth} onChange={e => setComparisonMonth(e.target.value)}>
                    {Object.keys(filteredData.monthly_stats).map(m => (
                        <option key={m} value={m}>{monthNames[m - 1]}</option>
                    ))}
                </select>
            </div>
        </div>
    </div>

    {/* Graphiques côte à côte */}
    <div className="charts-container" style={{ display: 'flex', gap: '20px' }}>
        {/* Line chart à gauche */}
        {lineDataPart1 && (
            <div className="chart" style={{ flex: 1 }}>
                <h4>Reactions Over Time</h4>
                <Line data={lineDataPart1} options={{ responsive: true }} />
            </div>
        )}

        {/* Bar chart à droite */}
        {barDataPart1 && (
            <div className="chart" style={{ flex: 1 }}>
                <h4>Monthly Comparison</h4>
                <Bar data={barDataPart1} options={{ responsive: true }} />
            </div>
        )}
    </div>
</div>



                {/* Partie 2 : Comparaison avec Sopra HR */}
                <div className="chart-section">
                    <h3>Comparaison avec Sopra HR</h3>
                    <div className="selector">
                        <label>Organisation à comparer :</label>
                        <select value={compareWithSopra} onChange={e => setCompareWithSopra(e.target.value)}>
                            {Object.keys(organizationMap)
                                .filter(id => id !== '18931')
                                .map(id => (
                                    <option key={id} value={id}>{organizationMap[id]}</option>
                                ))}
                        </select>
                    </div>
                    {barDataPart2 && <Bar data={barDataPart2} />}
                </div>
            </div>
        </DashboardLayout>
    );
};

export default StatisticsPage;
