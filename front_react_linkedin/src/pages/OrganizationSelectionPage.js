import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import './css/OrganizationSelectionPage.css';
import DashboardLayout from './DashboardLayout';

const OrganizationSelectionPage = () => {
    const navigate = useNavigate();
    const [organizations, setOrganizations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchOrganizations = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('http://localhost:8000/api/organizations', {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Non authentifié. Veuillez vous reconnecter.');
                }
                throw new Error(`Erreur ${response.status} lors de la récupération des organisations`);
            }

            const data = await response.json();
            if (Array.isArray(data)) setOrganizations(data);
            else if (data.organizations) setOrganizations(data.organizations);
            else setOrganizations([]);
        } catch (err) {
            setError(err.message || 'Erreur inconnue');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchOrganizations();
    }, []);

    const handleSelection = (id) => {
        if (id === 140410) navigate(`/organization/${id}`);
        else navigate(`/OtherOrganization/${id}`);
    };

    const formatFollowers = (count) => {
        if (!count && count !== 0) return '0';
        if (count >= 1000000) return (count / 1000000).toFixed(1) + 'M';
        if (count >= 1000) return (count / 1000).toFixed(1) + 'K';
        return count.toString();
    };

    const getInitial = (name) => {
        if (!name) return 'O';
        return typeof name === 'string' ? name.charAt(0).toUpperCase() : 'O';
    };

    const getName = (org) => {
        if (!org) return 'Organisation';

        if (org.name && typeof org.name === 'object' && org.name.localized && org.name.preferredLocale) {
            const locale = org.name.preferredLocale;
            const key = `${locale.language}_${locale.country}`;
            return org.name.localized[key] || 'Organisation';
        }

        return org.name || org.display_name || org.username || 'Organisation';
    };

    const getStat = (org, key) => {
        if (!org) return 0;
        return org[key] ?? org[key.replace('_count', '')] ?? 0;
    };

    if (loading) {
        return (
            <DashboardLayout>
                <div className="organization-selection-page">
                    <div className="loading-container">
                        <div className="loading-spinner"></div>
                        <p>Chargement des organisations...</p>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    if (error) {
        return (
            <DashboardLayout>
                <div className="organization-selection-page">
                    <div className="error-container">
                        <div className="error-icon">⚠️</div>
                        <h2>Erreur de chargement</h2>
                        <p>{error}</p>
                        <button className="retry-button" onClick={fetchOrganizations}>
                            Réessayer
                        </button>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout>
            <div className="organization-selection-page">
                <div className="organization-header">
                    <h1 className="page-title">Sélectionnez une Organisation</h1>
                    <p className="page-subtitle">
                        Choisissez une organisation pour accéder à son tableau de bord
                    </p>
                </div>

                <div className="organization-grid">
                    {organizations.map((org) => {
                        const organizationName = getName(org);
                        const initial = getInitial(organizationName);

                        return (
                            <div
                                key={org.id}
                                className="organization-card"
                                onClick={() => handleSelection(org.id)}
                            >
                                <div className="organization-avatar">{initial}</div>
                                <div className="organization-info">
                                    <h3 className="organization-name">{organizationName}</h3>
                                    <div className="organization-stats">
                                        {/* Followers */}
                                        <div className="stat-item">
                                            <span className="stat-value">
                                                {formatFollowers(getStat(org, 'followers_count') || getStat(org, 'followers'))}
                                            </span>
                                            <span className="stat-label">Abonnés</span>
                                        </div>
                                        {/* Publications */}
                                        {(getStat(org, 'posts_count') || getStat(org, 'posts')) > 0 && (
                                            <div className="stat-item">
                                                <span className="stat-value">
                                                    {formatFollowers(getStat(org, 'posts_count') || getStat(org, 'posts'))}
                                                </span>
                                                <span className="stat-label">Publications</span>
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="selection-indicator">
                                    <div className="arrow-icon">→</div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </DashboardLayout>
    );
};

export default OrganizationSelectionPage;
