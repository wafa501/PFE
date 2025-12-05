import React, { useEffect, useState } from 'react';
import { Route, Navigate } from 'react-router-dom';

const PrivateRoute = ({ element: Component, ...rest }) => {
    const [isAuthenticated, setIsAuthenticated] = useState(null);

    useEffect(() => {
        const checkAuth = async () => {
            try {
                const response = await fetch('/api/check-auth', {
                    method: 'GET',
                    headers: {
                        'Authorization': localStorage.getItem('linked_in_token')
                    }
                });
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

    return (
        <Route
            {...rest}
            element={isAuthenticated ? <Component /> : <Navigate to="/login" />}
        />
    );
};

export default PrivateRoute;
