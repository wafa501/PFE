import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import DashboardLayout from './DashboardLayout';
import './css/MyOrganizationDetails.css'; 

const MyOrganizationDetails = () => {
    const { id } = useParams();
    const [organization, setOrganization] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchOrganizationData = async () => {
            try {
                const response = await fetch(`http://localhost:8000/Myorganization/${id}`);

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                setOrganization(data);
            } catch (error) {
                setError(error.message);
            }
        };

        fetchOrganizationData();
    }, [id]);

    if (error) return <DashboardLayout><div className="organization-details">Error: {error}</div></DashboardLayout>;
    if (!organization) return <DashboardLayout><div className="organization-details">Loading...</div>;</DashboardLayout>

    const defaultLocale = organization.default_locale || {};

    return (
        <DashboardLayout>
            <div className="organization-details">
                <h1>{organization.name?.localized?.fr_FR || 'Unknown Organization'}</h1>
                <p><span>Vanity Name:</span> {organization.vanity_name}</p>
                <p><span>Followers:</span> {organization.followers}</p>
                <p><span>Localized Name:</span> {organization.localized_name}</p>
                <p><span>Staff Count Range:</span> {organization.staff_count_range}</p>
                <p><span>Groups:</span> {organization.groups.length ? organization.groups.join(', ') : 'N/A'}</p>
                <p><span>Version Tag:</span> {organization.version_tag}</p>
                <p><span>Organization Type:</span> {organization.organization_type}</p>
                <p><span>Primary Organization Type:</span> {organization.primary_organization_type}</p>
                <p><span>Default Locale Country:</span> {defaultLocale.country || 'N/A'}</p>
                <p><span>Default Locale Language:</span> {defaultLocale.language || 'N/A'}</p>
                <p><span>Industries:</span> {organization.industries.length ? organization.industries.join(', ') : 'N/A'}</p>
                <p><span>Alternative Names:</span> {organization.alternative_names.length ? organization.alternative_names.join(', ') : 'N/A'}</p>
                <p><span>Specialties:</span> {organization.specialties.length ? organization.specialties.join(', ') : 'N/A'}</p>
                <p><span>Localized Specialties:</span> {organization.localized_specialties.length ? organization.localized_specialties.join(', ') : 'N/A'}</p>
                <p><span>Locations:</span> {organization.locations.length ? organization.locations.join(', ') : 'N/A'}</p>
                <p><span>LinkedIn ID:</span> {organization.linkedin_id || 'N/A'}</p>
            </div>
        </DashboardLayout>
    );
};

export default MyOrganizationDetails;
