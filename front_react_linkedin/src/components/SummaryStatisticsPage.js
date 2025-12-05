import React from 'react';
import { Line, Bar } from 'react-chartjs-2';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const SummaryStatisticsPage = ({ data }) => {
  const months = Object.keys(data.monthly_stats);
  const uniqueImpressions = months.map(month => data.monthly_stats[month].uniqueImpressionsCount);
  const engagement = months.map(month => data.monthly_stats[month].engagement);

  const impressionsData = {
    labels: months,
    datasets: [
      {
        label: 'Unique Impressions',
        data: uniqueImpressions,
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderWidth: 1,
      },
    ],
  };

  const engagementData = {
    labels: months,
    datasets: [
      {
        label: 'Engagement',
        data: engagement,
        borderColor: 'rgba(153, 102, 255, 1)',
        backgroundColor: 'rgba(153, 102, 255, 0.2)',
        borderWidth: 1,
      },
    ],
  };

  return (
    <div>
      <h1>Statistics for {data.year}</h1>
      <div>
        <h2>Unique Impressions</h2>
        <Line data={impressionsData} />
      </div>
      <div>
        <h2>Engagement</h2>
        <Bar data={engagementData} />
      </div>
    </div>
  );
};

export default SummaryStatisticsPage;
