import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Line } from 'react-chartjs-2';
import DashboardLayout from './DashboardLayout';
import 'chart.js/auto';

const URN_TO_NAME = {
  '18931': 'SopraHR',
  '1463': 'ADP',
  '38256': 'Vermeg',
  '71370828': 'Teamlink',
};

const MONTHS_IN_FRENCH = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
];

const PredictionPage = () => {
  const [selectedOrganization, setSelectedOrganization] = useState('18931');
  const [predictedStats, setPredictedStats] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [selectedMonth, setSelectedMonth] = useState(null);

  // Récupérer les données depuis l'API
  const fetchPredictedStats = async (organizationId) => {
    try {
      setLoading(true);
      setError(null);
      const response = await axios.get(`http://localhost:8000/api/MyStats`, { withCredentials: true });

      const orgData = response.data.find(item => item.organization.toString() === organizationId);
      if (orgData) {
        setPredictedStats(orgData.monthly_stats);
      } else {
        setError('No prediction data available for this organization.');
      }
      setLoading(false);
    } catch (err) {
      console.error(err);
      setLoading(false);
      setError('Error fetching prediction data.');
    }
  };

  useEffect(() => {
    if (selectedOrganization) fetchPredictedStats(selectedOrganization);
  }, [selectedOrganization]);

  // Création des données pour le chart
  const createChartData = () => {
    if (!selectedMonth || !predictedStats || !predictedStats[selectedMonth]) return {};
    const monthStats = predictedStats[selectedMonth];

    const allValues = [
      monthStats.uniqueImpressionsCount || 0,
      monthStats.shareCount || 0,
      monthStats.shareMentionsCount || 0,
      monthStats.engagement || 0,
      monthStats.clickCount || 0,
      monthStats.likeCount || 0,
      monthStats.commentCount || 0,
    ];

    return {
      labels: ['Unique Impressions', 'Shares', 'Share Mentions', 'Engagement', 'Clicks', 'Likes', 'Comments'],
      datasets: [
        {
          label: 'Predicted Stats',
          data: allValues,
          borderColor: 'rgba(75,192,192,1)',
          backgroundColor: 'rgba(75,192,192,0.2)',
          fill: true,
          tension: 0.3,
        }
      ]
    };
  };

  return (
    <DashboardLayout>
      <div>
        <h2>Predictions for {URN_TO_NAME[selectedOrganization]}</h2>

        {loading && <p>Loading data...</p>}
        {error && !loading && <p style={{ color: 'red' }}>{error}</p>}

        {/* Organization Selector */}
        <div>
          <label>Organization: </label>
          <select value={selectedOrganization} onChange={e => setSelectedOrganization(e.target.value)}>
            {Object.keys(URN_TO_NAME).map(id => (
              <option key={id} value={id}>{URN_TO_NAME[id]}</option>
            ))}
          </select>
        </div>

        {/* Month Selector */}
       <div>
  <label>Month: </label>
  <select value={selectedMonth || ''} onChange={e => setSelectedMonth(e.target.value)}>
    <option value="">Select a Month</option>
    {predictedStats && Object.keys(predictedStats).map(m => {
        const monthIndex = parseInt(m, 10) - 12; // adapter si tes mois commencent à 12
        return <option key={m} value={m}>{MONTHS_IN_FRENCH[monthIndex]}</option>
    })}
  </select>
</div>
       {selectedMonth && predictedStats && predictedStats[selectedMonth] && (
  <div>
    <h3>Month {MONTHS_IN_FRENCH[parseInt(selectedMonth, 10) - 12]} - Stats</h3>
    <Line data={createChartData()} />
  </div>
)}
      </div>
    </DashboardLayout>
  );
};

export default PredictionPage;
