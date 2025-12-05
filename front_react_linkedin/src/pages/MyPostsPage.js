import React, { useEffect, useState, useCallback, useMemo } from 'react';
import { useParams } from 'react-router-dom';
import DashboardLayout from './DashboardLayout';
import axios from 'axios';
import './css/MyPosts.css';

const MyPostsPage = () => {
    const { id } = useParams();
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [visibleRows, setVisibleRows] = useState(20);
    const [expandedPost, setExpandedPost] = useState(null);

    const ROWS_PER_BATCH = 10;
    const INITIAL_ROWS = 20;


    useEffect(() => {
        const fetchPostsData = async () => {
            try {
                const response = await axios.get(`http://localhost:8000/getMyPosts/${id}`, {
                    withCredentials: true,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    timeout: 10000
                });

                console.log('API Response:', response.data);

                if (response.data.success && Array.isArray(response.data.posts)) {
                    setPosts(response.data.posts);
                } else if (Array.isArray(response.data)) {
                    setPosts(response.data);
                } else {
                    setPosts([]);
                }

            } catch (err) {
                console.error('Axios error details:', err);
                
                if (err.response) {
                    switch (err.response.status) {
                        case 401:
                            setError('Authentication required. Please log in.');
                            break;
                        case 404:
                            setError('User or posts not found.');
                            break;
                        case 500:
                            setError('Server error: ' + (err.response.data.error || 'Please try again.'));
                            break;
                        default:
                            setError(err.response.data?.error || `Error ${err.response.status}`);
                    }
                } else if (err.request) {
                    setError('Network error. Cannot connect to server.');
                } else {
                    setError('Error: ' + err.message);
                }
            } finally {
                setLoading(false);
            }
        };

        if (id && !isNaN(id)) {
            fetchPostsData();
        } else {
            setError('Invalid user ID');
            setLoading(false);
        }
    }, [id]);

    // Lazy loading avec Intersection Observer
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && visibleRows < posts.length) {
                    setVisibleRows(prev => Math.min(prev + ROWS_PER_BATCH, posts.length));
                }
            },
            { threshold: 0.1 }
        );

        const sentinel = document.getElementById('load-more-sentinel');
        if (sentinel) {
            observer.observe(sentinel);
        }

        return () => {
            if (sentinel) {
                observer.unobserve(sentinel);
            }
        };
    }, [posts.length, visibleRows]);

    // Posts visibles pour le lazy loading
    const visiblePosts = useMemo(() => {
        return posts.slice(0, visibleRows);
    }, [posts, visibleRows]);

    const toggleExpand = useCallback((postId) => {
        setExpandedPost(expandedPost === postId ? null : postId);
    }, [expandedPost]);

    const formatDate = useCallback((dateString) => {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString('fr-FR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }, []);

    const retryFetch = () => {
        setLoading(true);
        setError(null);
        setVisibleRows(INITIAL_ROWS);
        window.location.reload();
    };
    const cleanUrl = (url) => {
    if (!url) return null;

    try {
        url = url.trim();
        url = decodeURIComponent(url);
        url = encodeURI(url);
        return url;
    } catch (e) {
        console.warn("URL invalid:", url);
        return null;
    }
};

// Composant image
const PostImage = ({ imageUrl }) => {
    const url = cleanUrl(imageUrl);
    if (!url) return <span className="no-media">No Image</span>;

    return (
        <div className="media-container">
            <img 
                src={url}
                alt="Post"
                className="post-image"
                loading="lazy"
                onError={(e) => {
                    e.target.src = "https://via.placeholder.com/150?text=Invalid+Image";
                }}
            />
        </div>
    );
};

// Composant vidéo
const PostVideo = ({ videoUrl }) => {
    const url = cleanUrl(videoUrl);
    if (!url) return <span className="no-media">No Video</span>;

    return (
        <div className="media-container">
            <video
                src={url}
                controls
                className="post-video"
                preload="metadata"
            />
        </div>
    );
};


    if (loading) {
        return (
            <DashboardLayout>
                <div className="loading-container">
                    <div className="loading-content">
                        <div className="loading-spinner"></div>
                        <p>Loading posts...</p>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    if (error) {
        return (
            <DashboardLayout>
                <div className="error-container">
                    <div className="error-content">
                        <div className="error-icon">⚠️</div>
                        <h3>Error Loading Posts</h3>
                        <p>{error}</p>
                        <button onClick={retryFetch} className="retry-btn">
                            Try Again
                        </button>
                    </div>
                </div>
            </DashboardLayout>
        );
    }

    return (
        <DashboardLayout>
            <div className="posts-container">
                <div className="posts-header">
                    <h1>My Posts</h1>
                    <div className="posts-stats">
                        <span className="posts-count">{posts.length} posts</span>
                        {visibleRows < posts.length && (
                            <span className="loading-progress">
                                Showing {visibleRows} of {posts.length}
                            </span>
                        )}
                    </div>
                </div>
                
                {posts.length === 0 ? (
                    <div className="no-posts">
                        <div className="no-posts-icon">📝</div>
                        <h3>No Posts Yet</h3>
                        <p>Start sharing your thoughts and experiences!</p>
                    </div>
                ) : (
                    <>
                        <div className="posts-table-container">
                            <table className="posts-table">
                                <thead>
                                    <tr>
                                        <th>Content</th>
                                        <th>Media</th>
                                        <th>Published</th>
                                        <th>Last Modified</th>
                                        <th>Engagement</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {visiblePosts.map((post, index) => (
                                        <tr 
                                            key={post.id || index}
                                            className={`post-row ${expandedPost === post.id ? 'expanded' : ''}`}
                                        >
                                            <td className="content-cell">
                                                <div className="post-content">
                                                    <div 
                                                        className={`commentary ${post.commentary && post.commentary.length > 150 ? 'expandable' : ''}`}
                                                        onClick={() => post.commentary && post.commentary.length > 150 && toggleExpand(post.id)}
                                                    >
                                                        {post.commentary || 'No content'}
                                                        {post.commentary && post.commentary.length > 150 && (
                                                            <span className="expand-indicator">
                                                                {expandedPost === post.id ? 'Show less' : 'Show more'}
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td className="media-cell">
                                                <PostImage imageUrl={post.image_url} />
                                                <PostVideo videoUrl={post.video_url} />
                                            </td>
                                            
                                            <td className="date-cell">
                                                <div className="date-info">
                                                    <span className="date-label">Published:</span>
                                                    <span className="date-value">{formatDate(post.published_at)}</span>
                                                </div>
                                            </td>
                                            
                                            <td className="date-cell">
                                                <div className="date-info">
                                                    <span className="date-label">Modified:</span>
                                                    <span className="date-value">{formatDate(post.last_modified_at)}</span>
                                                </div>
                                            </td>
                                            
                                            <td className="engagement-cell">
                                                <div className="engagement-stats">
                                                    <div className="stat-item">
                                                        <span className="stat-icon">❤️</span>
                                                        <span className="stat-value">{post.likes_count ?? 0}</span>
                                                    </div>
                                                    <div className="stat-item">
                                                        <span className="stat-icon">💬</span>
                                                        <span className="stat-value">{post.comments_count ?? 0}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td className="status-cell">
                                                <div className="status-info">
                                                    <span className={`lifecycle-state ${post.lifecycle_state?.toLowerCase()}`}>
                                                        {post.lifecycle_state || 'N/A'}
                                                    </span>
                                                    <span className={`visibility ${post.visibility?.toLowerCase()}`}>
                                                        {post.visibility || 'N/A'}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            
                            {/* Sentinel pour le lazy loading */}
                            {visibleRows < posts.length && (
                                <div id="load-more-sentinel" className="load-more-sentinel">
                                    <div className="loading-more">
                                        <div className="small-spinner"></div>
                                        Loading more posts...
                                    </div>
                                </div>
                            )}
                        </div>
                        
                        {/* Résumé du chargement */}
                        {visibleRows >= posts.length && posts.length > 0 && (
                            <div className="load-complete">
                                <p>All {posts.length} posts loaded</p>
                            </div>
                        )}
                    </>
                )}
            </div>
        </DashboardLayout>
    );
};

export default MyPostsPage;