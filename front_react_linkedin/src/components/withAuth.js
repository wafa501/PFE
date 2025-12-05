import React, { useEffect, useState } from 'react';
import { Navigate } from 'react-router-dom';

const withAuth = (WrappedComponent) => {
    return (props) => {
        const [isAuthenticated, setIsAuthenticated] = useState(null);

        useEffect(() => {
            const checkAuth = async () => {
                try {
                    const response = await fetch('http://localhost:8000/api/check-auth', {
                        method: 'GET',
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    setIsAuthenticated(data.authenticated);
                } catch (error) {
                    console.error('Error checking authentication:', error);
                    setIsAuthenticated(false);
                }
            };

            checkAuth();
        }, []);

        if (isAuthenticated === null) {
            return <div>Loading...</div>;
        }

        return isAuthenticated ? <WrappedComponent {...props} /> : <Navigate to="/login" />;
    };
};

export default withAuth;
