import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import DashboardLayout from './DashboardLayout';
import axios from 'axios';
import './css/OtherOrganizationDetails.css';

const OtherOrganizationDetails = () => {
    const { id } = useParams();
    const [organization, setOrganization] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOrganization = async () => {
            try {
                const response = await axios.get(`http://localhost:8000/otherOrganization/${id}`, {
                    withCredentials: true,
                    headers: { 'Accept': 'application/json' },
                });
                setOrganization(response.data || null);
            } catch (err) {
                console.error('Axios error:', err);
                setError(err.response?.data?.error || err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchOrganization();
    }, [id]);

    if (loading) return (
        <DashboardLayout>
            <div className="loading-container">
                <div className="loading-content">
                    <div className="loading-spinner"></div>
                    Loading organization details...
                </div>
            </div>
        </DashboardLayout>
    );
    
    if (error) return (
        <DashboardLayout>
            <div className="error-container">
                <div className="error-content">Error: {error}</div>
            </div>
        </DashboardLayout>
    );
    
    if (!organization) return (
        <DashboardLayout>
            <div className="no-data-container">
                <div className="no-data-content">No organization data found.</div>
            </div>
        </DashboardLayout>
    );

    // Sécurisation des champs
    const {
        name = {},
        followers = 0,
        vanity_name = 'N/A',
        localized_name = 'N/A',
        primary_organization_type = 'N/A',
        locations = [],
        linkedin_id = 'N/A',
        localized_website = null,
        logo_v2 = {}
    } = organization;

    const safeLocations = Array.isArray(locations) ? locations : [];
    const localizedFr = name?.localized?.fr_FR || 'N/A';
    const localizedEn = name?.localized?.en_US || 'N/A';
    const country = name?.preferredLocale?.country || 'N/A';
    const language = name?.preferredLocale?.language || 'N/A';

    return (
        <DashboardLayout>
            <div className="organization-details">
                <div className="details-header">
                    <div className="org-title-section">
                        <h1 className="org-main-title">
                            {localized_name || vanity_name || 'Unknown Organization'}
                        </h1>
                        <div className="org-followers">
                            👥 {followers.toLocaleString()} followers
                        </div>
                    </div>
                </div>

                <div className="details-grid">
                    <div className="info-card">
                        <h3 className="card-title">Basic Information</h3>
                        <div className="info-item">
                            <span className="info-label">Vanity Name</span>
                            <span className="info-value">{vanity_name}</span>
                        </div>
                        <div className="info-item">
                            <span className="info-label">Organization Type</span>
                            <span className="info-value">{primary_organization_type}</span>
                        </div>
                        <div className="info-item">
                            <span className="info-label">LinkedIn ID</span>
                            <span className="info-value">{linkedin_id}</span>
                        </div>
                        <div className="info-item">
                            <span className="info-label">Website</span>
                            <span className="info-value">
                                {localized_website ? (
                                    <a 
                                        href={localized_website} 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        className="website-link"
                                    >
                                        🌐 Visit Website
                                    </a>
                                ) : 'N/A'}
                            </span>
                        </div>
                    </div>

                    <div className="info-card">
                        <h3 className="card-title">Localization</h3>
                        <div className="info-item">
                            <span className="info-label">French Name</span>
                            <span className="info-value">{localizedFr}</span>
                        </div>
                        <div className="info-item">
                            <span className="info-label">English Name</span>
                            <span className="info-value">{localizedEn}</span>
                        </div>
                        <div className="info-item">
                            <span className="info-label">Country</span>
                            <span className="info-value">{country}</span>
                        </div>
                        <div className="info-item">
                            <span className="info-label">Language</span>
                            <span className="info-value">{language}</span>
                        </div>
                    </div>
                </div>

                {safeLocations.length > 0 && (
                    <div className="locations-section">
                        <h3 className="card-title">Locations</h3>
                        <div className="locations-grid">
                            {safeLocations.map((loc, index) => (
                                <div key={index} className="location-card">
                                    <p className="location-field">
                                        <span className="location-label">📍 Address:</span>
                                        {[
                                            loc?.address?.line1,
                                            loc?.address?.line2,
                                            loc?.address?.city,
                                            loc?.address?.country
                                        ].filter(Boolean).join(', ') || 'N/A'}
                                    </p>
                                    <p className="location-field">
                                        <span className="location-label">📞 Phone:</span>
                                        {loc?.phoneNumber1?.number || 'N/A'}
                                    </p>
                                    <p className="location-field">
                                        <span className="location-label">🏢 Type:</span>
                                        {loc?.locationType || 'N/A'}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </DashboardLayout>
    );
};

export default OtherOrganizationDetails;