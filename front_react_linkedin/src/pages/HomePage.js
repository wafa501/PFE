/* eslint-disable jsx-a11y/img-redundant-alt */
import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import DashboardLayout from './DashboardLayout';
import './css/MyPosts.css';

const HomePage = () => {
    const { id } = useParams();
    const [posts, setPosts] = useState([]);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchPostsData = async () => {
            try {
                const response = await fetch(`http://localhost:8000/Mystats`);

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const data = await response.json();
                setPosts(data);
            } catch (error) {
                setError(error.message);
            }
        };

        fetchPostsData();
    }, [id]);

    if (error) return <DashboardLayout><div className="posts-details">Error: {error}</div></DashboardLayout>;
    if (!posts.length) return <DashboardLayout><div className="posts-details">Loading...</div>;</DashboardLayout>

    return (
        <DashboardLayout>
          <p>4</p>
        </DashboardLayout>
    );
};

export default HomePage;
