import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { Chart as ChartJS, BarElement, LineElement, CategoryScale, LinearScale, Title, Tooltip, Legend } from 'chart.js';
import { Bar, Line } from 'react-chartjs-2';
import DashboardLayout from './DashboardLayout';

ChartJS.register(
  BarElement,
  LineElement,
  CategoryScale,
  LinearScale,
  Title,
  Tooltip,
  Legend
);

const Dashboard = () => {
  const id = "108194077";
  const [organization, setOrganization] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [chartType, setChartType] = useState('line');  
  const [statsType, setStatsType] = useState('total'); 
  const [selectedCategory, setSelectedCategory] = useState('country');

const chartOptions = useMemo(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true,
        position: 'top',
      },
      tooltip: {
        mode: 'index',
        intersect: false,
      },
    },
    scales: {
      x: {
        display: true,
        title: {
          display: true,
        },
      },
      y: {
        display: true,
        beginAtZero: true,
      },
    },
    animation: {
      duration: 400,
      easing: 'linear'
    },
    hover: {
      animationDuration: 0
    },
    responsiveAnimationDuration: 0
  }), []);

  const handleChartTypeChange = useCallback((type) => {
    setChartType(type);
  }, []);

  const handleStatsTypeChange = useCallback((type) => {
    setStatsType(type);
  }, []);

  const handleCategoryChange = useCallback((e) => {
    setSelectedCategory(e.target.value);
  }, []);

  // Rendu des boutons avec gestion stable des classes
  const renderChartTypeButtons = useCallback(() => (
    <div className="button-container">
      <h3>Type de Graphique</h3>
      <button 
        onClick={() => handleChartTypeChange('line')}
        className={chartType === 'line' ? 'active' : ''}
        type="button"
      >
        Graphique Linéaire
      </button>
      <button 
        onClick={() => handleChartTypeChange('bar')}
        className={chartType === 'bar' ? 'active' : ''}
        type="button"
      >
        Graphique à Barres
      </button>
    </div>
  ), [chartType, handleChartTypeChange]);

  const renderStatsTypeButtons = useCallback(() => (
    <div className="button-container">
      <h3>Type de Statistiques</h3>
      <button 
        onClick={() => handleStatsTypeChange('total')}
        className={statsType === 'total' ? 'active' : ''}
        type="button"
      >
        Vues Totales
      </button>
      <button 
        onClick={() => handleStatsTypeChange('byCategory')}
        className={statsType === 'byCategory' ? 'active' : ''}
        type="button"
      >
        Par Catégorie
      </button>
    </div>
  ), [statsType, handleStatsTypeChange]);

  const renderCategorySelect = useCallback(() => (
    <div className="button-container">
      <h3>Catégorie</h3>
      <select 
        value={selectedCategory} 
        onChange={handleCategoryChange}
        className="category-select"
      >
        <option value="country" className="black">Par Pays</option>
        <option value="seniority" className="black">Par Séniorité</option>
        <option value="industry" className="black">Par Industrie</option>
        <option value="targetedContent" className="black">Par Contenu Ciblé</option>
        <option value="staffCount" className="black">Par Taille d'Entreprise</option>
        <option value="function" className="black">Par Fonction</option>
        <option value="region" className="black">Par Région</option>
      </select>
    </div>
  ), [selectedCategory, handleCategoryChange]);

  useEffect(() => {
    const fetchOrganizationData = async () => {
      try {
        setLoading(true);
        const response = await fetch(`http://localhost:8000/Myorganization/${id}`, {
          method: 'GET',
          credentials: 'include',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
        });

        if (!response.ok) {
          if (response.status === 401) {
            throw new Error('Unauthorized - Please log in');
          } else if (response.status === 404) {
            throw new Error('Organization not found');
          } else {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
        }

        const contentType = response.headers.get('Content-Type');
        if (!contentType || !contentType.includes('application/json')) {
          throw new Error('Expected JSON response but got: ' + contentType);
        }

        const data = await response.json();
        setOrganization(data);
      } catch (error) {
        console.error('Fetch error:', error);
        setError(error.message);
      } finally {
        setLoading(false);
      }
    };

    fetchOrganizationData();
  }, [id]);

  const parseJSONSafe = useCallback((jsonString) => {
    if (!jsonString) return {};
    try {
      return typeof jsonString === 'string' ? JSON.parse(jsonString) : jsonString;
    } catch (error) {
      console.error('JSON parsing error:', error);
      return {};
    }
  }, []);

  const organizationData = useMemo(() => {
    if (!organization) {
      return {
        totalPageStatistics: { views: {} },
        pageStatisticsByCountry: {},
        pageStatisticsBySeniority: {},
        pageStatisticsByIndustry: {},
        pageStatisticsByTargetedContent: {},
        pageStatisticsByStaffCountRange: {},
        pageStatisticsByFunction: {},
        pageStatisticsByRegion: {},
      };
    }
    
    return {
      totalPageStatistics: parseJSONSafe(organization.total_page_statistics) || { views: {} },
      pageStatisticsByCountry: parseJSONSafe(organization.page_statistics_by_country) || {},
      pageStatisticsBySeniority: parseJSONSafe(organization.page_statistics_by_seniority) || {},
      pageStatisticsByIndustry: parseJSONSafe(organization.page_statistics_by_industry) || {},
      pageStatisticsByTargetedContent: parseJSONSafe(organization.page_statistics_by_targeted_content) || {},
      pageStatisticsByStaffCountRange: parseJSONSafe(organization.page_statistics_by_staff_count_range) || {},
      pageStatisticsByFunction: parseJSONSafe(organization.page_statistics_by_function) || {},
      pageStatisticsByRegion: parseJSONSafe(organization.page_statistics_by_region) || {},
    };
  }, [organization, parseJSONSafe]);

  const totalPageStatisticsData = useMemo(() => {
    const { totalPageStatistics } = organizationData;
    
    const views = totalPageStatistics?.views || {};
    const labels = Object.keys(views);
    const dataValues = Object.values(views).map(view => 
      typeof view === 'object' ? (view.pageViews || view.views || 0) : (view || 0)
    );

    if (labels.length === 0) {
      return {
        labels: ['Aucune donnée disponible'],
        datasets: [
          {
            label: 'Total Page Views',
            data: [0],
            backgroundColor: 'rgba(200, 200, 200, 0.6)',
            borderColor: 'rgba(200, 200, 200, 1)',
            borderWidth: 2,
            tension: 0.1,
          },
        ],
      };
    }

    return {
      labels,
      datasets: [
        {
          label: 'Total Page Views',
          data: dataValues,
          backgroundColor: 'rgba(54, 162, 235, 0.6)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2,
          tension: 0.1,
        },
      ],
    };
  }, [organizationData]);

  // Données par catégorie avec gestion des erreurs
  const categoryChartData = useMemo(() => {
    const categoryMap = {
      country: { data: organizationData.pageStatisticsByCountry, label: 'Par Pays' },
      seniority: { data: organizationData.pageStatisticsBySeniority, label: 'Par Séniorité' },
      industry: { data: organizationData.pageStatisticsByIndustry, label: 'Par Industrie' },
      targetedContent: { data: organizationData.pageStatisticsByTargetedContent, label: 'Par Contenu Ciblé' },
      staffCount: { data: organizationData.pageStatisticsByStaffCountRange, label: 'Par Taille d\'Entreprise' },
      function: { data: organizationData.pageStatisticsByFunction, label: 'Par Fonction' },
      region: { data: organizationData.pageStatisticsByRegion, label: 'Par Région' },
    };

    const selectedCategoryData = categoryMap[selectedCategory] || categoryMap.country;
    const categoryData = selectedCategoryData.data;

    if (!categoryData || Object.keys(categoryData).length === 0) {
      return {
        labels: ['Aucune donnée disponible'],
        datasets: [
          {
            label: selectedCategoryData.label,
            data: [0],
            backgroundColor: 'rgba(200, 200, 200, 0.6)',
            borderColor: 'rgba(200, 200, 200, 1)',
            borderWidth: 2,
            tension: chartType === 'line' ? 0.1 : undefined,
          },
        ],
      };
    }

    const labels = Object.keys(categoryData);
    const data = Object.values(categoryData).map(item => {
      if (typeof item === 'object') {
        return item.pageViews || item.views || 0;
      }
      return item || 0;
    });

    return {
      labels,
      datasets: [
        {
          label: selectedCategoryData.label,
          data,
          backgroundColor: 'rgba(75, 192, 192, 0.6)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 2,
          tension: chartType === 'line' ? 0.1 : undefined,
        },
      ],
    };
  }, [organizationData, selectedCategory, chartType]);

  // Données finales du graphique
  const chartData = useMemo(() => 
    statsType === 'total' ? totalPageStatisticsData : categoryChartData,
    [statsType, totalPageStatisticsData, categoryChartData]
  );

  const ChartComponent = chartType === 'line' ? Line : Bar;

  // États de chargement et d'erreur
  if (loading) {
    return (
      <DashboardLayout>
        <div className="organization-details loading">Chargement des données...</div>
      </DashboardLayout>
    );
  }

  if (error) {
    return (
      <DashboardLayout>
        <div className="organization-details error">Erreur: {error}</div>
      </DashboardLayout>
    );
  }

  if (!organization) {
    return (
      <DashboardLayout>
        <div className="organization-details">Aucune donnée d'organisation trouvée.</div>
      </DashboardLayout>
    );
  }
return (
    <DashboardLayout>
      <div className="organization-details">
        <h1>{organization.name?.localized?.fr_FR || organization.name || 'Organisation inconnue'}</h1>
        
        <div className="chart-options-container">
          <div className="">
            {renderChartTypeButtons()}
          </div>

          <div className="">
            {renderStatsTypeButtons()}
          </div>

          {statsType === 'byCategory' && (
            <div className="">
              {renderCategorySelect()}
            </div>
          )}
        </div>

        <div className="chart">
          <h3>
            {statsType === 'total' 
              ? 'Statistiques des Vues de Page Totales' 
              : `Statistiques ${categoryChartData.datasets[0].label}`}
          </h3>
          <div className="chart-container">
            <ChartComponent 
              data={chartData} 
              options={chartOptions} 
              key={`${chartType}-${statsType}-${selectedCategory}`}
            />
          </div>
        </div>
      </div>
    </DashboardLayout>
  );
};

export default Dashboard;