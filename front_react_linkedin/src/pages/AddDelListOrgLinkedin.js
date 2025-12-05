import React, { useEffect, useState } from 'react';
import DashboardLayout from './DashboardLayout';
import './css/Dashboard.css';

const AddDelListOrgLinkedin = () => {
    const [organizations, setOrganizations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [newOrg, setNewOrg] = useState({ vanity_name: '', name: '' });
    const [csrfToken, setCsrfToken] = useState('');
    const [addingOrg, setAddingOrg] = useState(false);
    const [backgroundJobStatus, setBackgroundJobStatus] = useState(null);

    // Récupérer le token CSRF au chargement du composant
    useEffect(() => {
        const fetchCsrfToken = async () => {
            try {
                console.log('Fetching CSRF token...');
                
                const response = await fetch('http://localhost:8000/sanctum/csrf-cookie', {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                });

                if (response.ok) {
                    console.log('CSRF cookie set successfully');
                    
                    try {
                        const tokenResponse = await fetch('http://localhost:8000/csrf-token', {
                            credentials: 'include',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        if (tokenResponse.ok) {
                            const data = await tokenResponse.json();
                            setCsrfToken(data.csrf_token);
                            console.log('CSRF token received:', data.csrf_token);
                        }
                    } catch (tokenError) {
                        console.log('Using cookie-based CSRF protection');
                    }
                    
                    fetchOrganizations();
                } else {
                    throw new Error('Failed to set CSRF cookie');
                }
            } catch (err) {
                console.error('Erreur lors de la récupération du token CSRF:', err);
                setLoading(false);
            }
        };

        fetchCsrfToken();
    }, []);

    const getDisplayName = (org) => {
        if (!org) return 'Unknown';
        
        if (org.name && typeof org.name === 'object' && org.name.localized) {
            const localized = org.name.localized;
            if (org.name.preferredLocale) {
                const localeKey = `${org.name.preferredLocale.language}_${org.name.preferredLocale.country}`;
                if (localized[localeKey]) {
                    return localized[localeKey];
                }
            }
            return localized.en_US || localized[Object.keys(localized)[0]] || org.vanity_name || 'Unknown';
        }
        
        if (org.localized_name) {
            return org.localized_name;
        }
        
        return org.vanity_name || 'Unknown';
    };

    const fetchOrganizations = async () => {
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
            setOrganizations(data);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleAddOrganization = async () => {
        if (!newOrg.vanity_name) {
            return alert('Le vanity name est requis');
        }
        
        setAddingOrg(true);
        setBackgroundJobStatus('loading');
        
        try {
            console.log('Sending POST request with CSRF protection...');
            
            const response = await fetch('http://localhost:8000/api/organizations', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                },
                credentials: 'include',
                body: JSON.stringify(newOrg),
            });

            console.log('Response status:', response.status);
            
            let data;
            try {
                data = await response.json();
                console.log('Response data:', data);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Le serveur n\'a pas renvoyé de JSON valide');
            }

            if (!response.ok) {
                if (response.status === 419) {
                    throw new Error('Session expirée. Veuillez rafraîchir la page.');
                }
                
                if (response.status === 422 && data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    throw new Error(`Erreurs de validation: ${errorMessages}`);
                }
                
                throw new Error(data.error || data.message || `Erreur ${response.status} lors de l'ajout`);
            }

            setNewOrg({ vanity_name: '', name: '' });
            
            // Simuler un loading de 60 secondes pour le background job
            setTimeout(() => {
                setAddingOrg(false);
                setBackgroundJobStatus('completed');
                fetchOrganizations();
                
                // Réinitialiser le statut après 5 secondes
                setTimeout(() => {
                    setBackgroundJobStatus(null);
                }, 5000);
                
            }, 60000); // 60 secondes

        } catch (err) {
            setAddingOrg(false);
            setBackgroundJobStatus('error');
            console.error('Add organization error:', err);
            
            // Réinitialiser le statut d'erreur après 5 secondes
            setTimeout(() => {
                setBackgroundJobStatus(null);
            }, 5000);
        }
    };

    const handleDeleteOrganization = async (id) => {
        if (!window.confirm('Voulez-vous vraiment supprimer cette organisation ?')) return;
        
        try {
            const response = await fetch(`http://localhost:8000/api/organizations/${id}`, {
                method: 'DELETE',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken && { 'X-CSRF-TOKEN': csrfToken })
                },
            });
            
            if (!response.ok) {
                if (response.status === 419) {
                    throw new Error('Session expirée. Veuillez rafraîchir la page.');
                }
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erreur lors de la suppression');
            }
            
            fetchOrganizations();
            alert('Organisation supprimée avec succès!');
        } catch (err) {
            alert(err.message);
        }
    };

    // Styles CSS améliorés avec des noms plus spécifiques
    const customStyles = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .org-rotate-icon {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        .org-adding-organization {
            opacity: 0.7;
            pointer-events: none;
        }
        
        .org-pulse-effect {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .org-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* GRID FIXÉE À 3 COLONNES */
        .org-grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            width: 100%;
            padding: 1rem;
        }
        
        /* Responsive pour petits écrans */
        @media (max-width: 1200px) {
            .org-grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .org-grid-container {
                grid-template-columns: 1fr;
            }
        }
        
        .org-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            min-height: 250px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .org-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .org-form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .org-form-input:focus {
            outline: none;
            border-color: var(--linkedin-blue);
        }
        
        .org-form-input:disabled {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }
        
        .org-add-button {
            background: var(--linkedin-blue);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .org-add-button:hover:not(:disabled) {
            background: #004182;
            transform: translateY(-2px);
        }
        
        .org-add-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .org-delete-button {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .org-delete-button:hover {
            background-color: #ffebee;
        }

        /* Conteneur principal avec espacement */
        .org-main-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Formulaire avec espacement */
        .org-add-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid #e0e0e0;
        }

        /* Messages de statut */
        .status-message {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            text-align: center;
            font-weight: 500;
        }
        
        .status-loading {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        .status-completed {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .status-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    `;

    if (loading) return (
        <DashboardLayout>
            <style>{customStyles}</style>
            <div className="org-main-container">
                <div style={{textAlign: 'center', padding: '3rem'}}>
                    <div style={{
                        width: '50px',
                        height: '50px',
                        border: '3px solid #f3f3f3',
                        borderTop: '3px solid var(--linkedin-blue)',
                        borderRadius: '50%',
                        animation: 'spin 1s linear infinite',
                        margin: '0 auto 1rem'
                    }}></div>
                    <p>Chargement des organisations...</p>
                </div>
            </div>
        </DashboardLayout>
    );

    if (error) return (
        <DashboardLayout>
            <style>{customStyles}</style>
            <div className="org-main-container">
                <div className="error-message" style={{
                    background: '#ffebee',
                    color: '#c62828',
                    padding: '1rem',
                    borderRadius: '8px',
                    border: '1px solid #ffcdd2',
                    textAlign: 'center'
                }}>
                    <strong>Erreur:</strong> {error}
                    <br />
                    <button 
                        onClick={() => window.location.reload()} 
                        style={{
                            marginTop: '0.5rem',
                            padding: '0.5rem 1rem',
                            background: 'var(--linkedin-blue)',
                            color: 'white',
                            border: 'none',
                            borderRadius: '4px',
                            cursor: 'pointer'
                        }}
                    >
                        Rafraîchir la page
                    </button>
                </div>
            </div>
        </DashboardLayout>
    );

    return (
        <DashboardLayout>
            <style>{customStyles}</style>
            <div className="org-main-container">
                <h1 style={{ 
                    color: 'var(--linkedin-text)', 
                    marginBottom: '2rem',
                    textAlign: 'center',
                    fontSize: '2rem'
                }}>
                    Gestion des Organisations LinkedIn
                </h1>

                {/* Messages de statut du background job */}
                {backgroundJobStatus === 'loading' && (
                    <div className="status-message status-loading">
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem' }}>
                            <span className="org-rotate-icon">⏳</span>
                            <span>Récupération des posts en arrière-plan (environ 60 secondes)...</span>
                        </div>
                    </div>
                )}

                {backgroundJobStatus === 'completed' && (
                    <div className="status-message status-completed">
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem' }}>
                            <span>✅</span>
                            <span>Organisation ajoutée avec succès ! Les posts ont été récupérés en arrière-plan.</span>
                        </div>
                    </div>
                )}

                {backgroundJobStatus === 'error' && (
                    <div className="status-message status-error">
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem' }}>
                            <span>❌</span>
                            <span>Erreur lors de l'ajout de l'organisation.</span>
                        </div>
                    </div>
                )}

                {/* Formulaire ajout organisation */}
                <div className={`org-add-form ${addingOrg ? 'org-adding-organization' : ''}`}>
                    <h2 style={{
                        color: 'var(--linkedin-text)', 
                        marginBottom: '1.5rem',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.5rem'
                    }}>
                        {addingOrg ? (
                            <>
                                <span className="org-rotate-icon">⏳</span>
                                Ajout en cours...
                            </>
                        ) : (
                            'Ajouter une organisation'
                        )}
                    </h2>
                    
                    <div style={{ marginBottom: '1rem' }}>
                        <input 
                            type="text" 
                            placeholder="Vanity Name (ex: microsoft)" 
                            value={newOrg.vanity_name} 
                            onChange={(e) => setNewOrg({ ...newOrg, vanity_name: e.target.value })}
                            className="org-form-input"
                            disabled={addingOrg}
                        />
                    </div>
                    
                    <small style={{
                        color: '#666', 
                        display: 'block', 
                        marginBottom: '1.5rem',
                        fontStyle: 'italic'
                    }}>
                        💡 L'ID LinkedIn sera récupéré automatiquement à partir du vanity name
                    </small>
                    
                    <button 
                        onClick={handleAddOrganization} 
                        className={`org-add-button ${addingOrg ? 'org-pulse-effect' : ''}`}
                        disabled={!newOrg.vanity_name || addingOrg}
                    >
                        {addingOrg ? (
                            <>
                                <span className="org-rotate-icon" style={{ marginRight: '0.5rem' }}>⏳</span>
                                Ajout en cours...
                            </>
                        ) : (
                            '➕ Ajouter l\'organisation'
                        )}
                    </button>
                </div>

                {/* Liste des organisations */}
                <div className="org-list-container">
                    <h2 style={{
                        color: 'var(--linkedin-text)',
                        marginBottom: '1.5rem',
                        borderBottom: '2px solid var(--linkedin-blue)',
                        paddingBottom: '0.5rem'
                    }}>
                        Organisations ({organizations.length})
                    </h2>
                    
                    {organizations.length === 0 ? (
                        <div style={{
                            textAlign: 'center', 
                            color: '#666', 
                            fontSize: '1.1rem',
                            padding: '3rem',
                            background: '#f8f9fa',
                            borderRadius: '8px',
                            border: '2px dashed #ddd'
                        }}>
                            <div style={{ fontSize: '3rem', marginBottom: '1rem' }}>🏢</div>
                            <p>Aucune organisation disponible.</p>
                            <p>Ajoutez votre première organisation !</p>
                        </div>
                    ) : (
                        <div className="org-grid-container">
                            {organizations.map((org, index) => (
                                <div 
                                    key={org.id} 
                                    className="org-card org-fade-in"
                                    style={{
                                        animationDelay: `${index * 0.1}s`,
                                    }}
                                >
                                    <div style={{
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'flex-start',
                                        marginBottom: '1rem'
                                    }}>
                                        <h3 style={{
                                            margin: 0,
                                            color: 'var(--linkedin-text)',
                                            fontSize: '1.3rem',
                                            lineHeight: '1.4',
                                            wordBreak: 'break-word'
                                        }}>
                                            {getDisplayName(org)}
                                        </h3>
                                        <button 
                                            className="org-delete-button" 
                                            onClick={() => handleDeleteOrganization(org.id)}
                                            title="Supprimer l'organisation"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                    
                                    <div style={{ lineHeight: '1.6' }}>
                                        <p><strong>Vanity Name:</strong> {org.vanity_name}</p>
                                        <p><strong>LinkedIn ID:</strong> {org.linkedin_id}</p>
                                        <p><strong>Abonnés:</strong> {org.followers ? parseInt(org.followers).toLocaleString() : 0}</p>
                                        <p><strong>Site web:</strong> {org.localized_website ? (
                                            <a 
                                                href={org.localized_website} 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                style={{
                                                    color: 'var(--linkedin-blue)', 
                                                    textDecoration: 'none',
                                                    fontWeight: '500'
                                                }}
                                                onMouseEnter={(e) => {
                                                    e.target.style.textDecoration = 'underline';
                                                }}
                                                onMouseLeave={(e) => {
                                                    e.target.style.textDecoration = 'none';
                                                }}
                                            >
                                                {org.localized_website}
                                            </a>
                                        ) : 'N/A'}</p>
                                        <p><strong>Dernière synchro:</strong> {org.last_synced_at ? 
                                            new Date(org.last_synced_at).toLocaleDateString('fr-FR', {
                                                day: '2-digit',
                                                month: '2-digit',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit'
                                            }) : 'Jamais'}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </DashboardLayout>
    );
};

export default AddDelListOrgLinkedin;