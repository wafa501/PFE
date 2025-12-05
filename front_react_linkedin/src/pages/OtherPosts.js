/* eslint-disable jsx-a11y/img-redundant-alt */
import React, { useEffect, useState, useMemo, useRef, useCallback } from 'react';
import { useParams } from 'react-router-dom';
import DashboardLayout from './DashboardLayout';
import axios from 'axios';
import './css/OtherPosts.css';

const OtherPosts = () => {
    const { id } = useParams();
    const [posts, setPosts] = useState([]);
    const [filteredPosts, setFilteredPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedAuthor, setSelectedAuthor] = useState('');
    const [selectedDate, setSelectedDate] = useState('');
    const [searchType, setSearchType] = useState('author');
    const [searchDateType, setSearchDateType] = useState('day');
    const [visibleRows, setVisibleRows] = useState(new Set());
    const [selectedCommentary, setSelectedCommentary] = useState(null);
    const [showCommentaryModal, setShowCommentaryModal] = useState(false);
    const [expandedCommentaries, setExpandedCommentaries] = useState(new Set());
    const observerRef = useRef(null);
    const tableContainerRef = useRef(null);

    const ORGANIZATION_NAME_MAP = {
        "urn:li:organization:1463": "ADP",
        "urn:li:organization:18931": "Sopra HR Software",
        "urn:li:organization:71370828": "TEAMLINK",
        "urn:li:organization:78338695": "LYNX ERP SOLUTIONS",
        "urn:li:organization:38256": "VERMEG for Banking & Insurance Software",
        "urn:li:organization:70279": "wevioo",

        
        "1463": "ADP",
        "18931": "Sopra HR Software",
        "71370828": "TEAMLINK",
        "78338695": "LYNX ERP SOLUTIONS",
        "38256": "VERMEG for Banking & Insurance Software",
        "70279": "wevioo",
        
        "urn:li:organization:1893156": "Attijari Bank",
        "urn:li:organization:7833869512": "BIAT",
        "urn:li:organization:3471370828": "Amen Bank",
        "urn:li:organization:9018931": "STB",
        "urn:li:organization:7137082890": "Banque de Tunisie",
    };

    const extractOrganizationInfo = (orgId) => {
        if (!orgId) return { name: 'Unknown Organization', id: 'unknown' };
        
        const mappedName = ORGANIZATION_NAME_MAP[orgId];
        if (mappedName) {
            return {
                name: mappedName,
                id: orgId,
                displayName: mappedName,
                orgNumber: orgId.includes('urn:li:organization:') ? orgId.split(':').pop() : orgId
            };
        }
        
        // Si c'est un URN LinkedIn, extraire l'ID numérique
        if (orgId.includes('urn:li:organization:')) {
            const orgNumber = orgId.split(':').pop();
            return {
                name: `Organization ${orgNumber}`,
                id: orgId,
                displayName: `Organization ${orgNumber}`,
                orgNumber: orgNumber
            };
        }
        
        // Si c'est déjà un ID numérique
        if (/^\d+$/.test(orgId)) {
            return {
                name: `Organization ${orgId}`,
                id: orgId,
                displayName: `Organization ${orgId}`,
                orgNumber: orgId
            };
        }
        
        // Si c'est un nom direct
        return {
            name: orgId,
            id: orgId,
            displayName: orgId,
            orgNumber: null
        };
    };

    // Fonction pour formater l'affichage de l'auteur
    const formatAuthorDisplay = useCallback((author) => {
        if (!author) return 'Unknown';
        const orgInfo = extractOrganizationInfo(author);
        return orgInfo.displayName;
    }, []);

    // Extraire dynamiquement les auteurs uniques des posts
    const uniqueAuthors = useMemo(() => {
        const authors = posts
            .map(post => post.author)
            .filter(author => author && author.trim() !== '')
            .filter((author, index, self) => self.indexOf(author) === index)
            .sort();
        
        console.log('Unique authors found:', authors);
        return authors;
    }, [posts]);

    // Extraire les organisations/entreprises des auteurs
    const organizations = useMemo(() => {
        const orgs = uniqueAuthors.map(author => {
            const orgInfo = extractOrganizationInfo(author);
            return {
                id: orgInfo.id,
                name: orgInfo.name,
                displayName: orgInfo.displayName,
                orgNumber: orgInfo.orgNumber,
                rawAuthor: author
            };
        });
        
        console.log('Organizations extracted:', orgs);
        return orgs;
    }, [uniqueAuthors]);

    // Statistiques des organisations
    const organizationStats = useMemo(() => {
        const stats = {};
        posts.forEach(post => {
            if (post.author) {
                const orgInfo = extractOrganizationInfo(post.author);
                const orgKey = orgInfo.id;
                
                if (!stats[orgKey]) {
                    stats[orgKey] = {
                        ...orgInfo,
                        postCount: 0,
                        latestPost: null
                    };
                }
                
                stats[orgKey].postCount++;
                
                // Trouver le post le plus récent
                if (post.published_at) {
                    const postDate = new Date(post.published_at);
                    if (!stats[orgKey].latestPost || postDate > new Date(stats[orgKey].latestPost)) {
                        stats[orgKey].latestPost = post.published_at;
                    }
                }
            }
        });
        
        return Object.values(stats).sort((a, b) => b.postCount - a.postCount);
    }, [posts]);

    // Vérifier si une URL vidéo est valide
    const isValidVideoUrl = useCallback((url) => {
        if (!url || url === "null" || url === "undefined" || url === "false") return false;
        if (typeof url !== 'string') return false;
        if (url.trim() === '') return false;
        return true;
    }, []);

    // Gérer l'expansion des commentaires
    const toggleCommentaryExpansion = useCallback((postId) => {
        setExpandedCommentaries(prev => {
            const newSet = new Set(prev);
            if (newSet.has(postId)) {
                newSet.delete(postId);
            } else {
                newSet.add(postId);
            }
            return newSet;
        });
    }, []);

    // Fonction pour formater le commentary avec option "See more"
    const formatCommentary = useCallback((commentary, postId) => {
        if (!commentary || commentary === 'null' || commentary === 'undefined') {
            return { text: 'No Content', isTruncated: false };
        }
        
        // Nettoyer le texte
        let cleanText = String(commentary).trim();
        const isExpanded = expandedCommentaries.has(postId);
        
        if (cleanText.length > 100 && !isExpanded) {
            return {
                text: cleanText.substring(0, 100) + '...',
                isTruncated: true,
                fullText: cleanText
            };
        }
        
        return {
            text: cleanText,
            isTruncated: cleanText.length > 100,
            fullText: cleanText
        };
    }, [expandedCommentaries]);

    // Lazy loading avec Intersection Observer amélioré
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const rowIndex = entry.target.getAttribute('data-row-index');
                        setVisibleRows(prev => {
                            const newSet = new Set(prev);
                            newSet.add(rowIndex);
                            return newSet;
                        });
                    }
                });
            },
            {
                rootMargin: '50px 0px 50px 0px',
                threshold: 0.01
            }
        );

        observerRef.current = observer;

        return () => {
            if (observerRef.current) {
                observerRef.current.disconnect();
            }
        };
    }, []);

    // Observer les nouvelles rows quand filteredPosts change
    useEffect(() => {
        if (!observerRef.current || filteredPosts.length === 0) return;

        const rows = document.querySelectorAll('.posts-table tbody tr[data-row-index]');
        rows.forEach(row => {
            observerRef.current.observe(row);
        });

        return () => {
            rows.forEach(row => {
                observerRef.current.unobserve(row);
            });
        };
    }, [filteredPosts]);

    // Charger initialement les premiers éléments
    useEffect(() => {
        if (filteredPosts.length > 0) {
            const initialVisible = new Set();
            // Charger les 10 premiers éléments immédiatement
            for (let i = 0; i < Math.min(10, filteredPosts.length); i++) {
                initialVisible.add(i.toString());
            }
            setVisibleRows(initialVisible);
        }
    }, [filteredPosts]);

    useEffect(() => {
        const fetchPostsData = async () => {
            try {
                const response = await axios.get('http://localhost:8000/api/posts', {
                    withCredentials: true,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    timeout: 10000
                });

                console.log('Posts API Response:', response.data);

                // Gérer différentes structures de réponse
                let postsData = [];
                if (Array.isArray(response.data)) {
                    postsData = response.data;
                } else if (response.data.posts && Array.isArray(response.data.posts)) {
                    postsData = response.data.posts;
                } else if (response.data.data && Array.isArray(response.data.data)) {
                    postsData = response.data.data;
                }

                console.log('Processed posts data:', postsData);
                setPosts(postsData);
                setFilteredPosts(postsData);

            } catch (error) {
                console.error('Error fetching posts:', error);
                
                if (error.response) {
                    switch (error.response.status) {
                        case 401:
                            setError('Authentication required. Please log in to view posts.');
                            break;
                        case 403:
                            setError('Access forbidden. You do not have permission to view these posts.');
                            break;
                        case 404:
                            setError('No posts found.');
                            break;
                        case 500:
                            setError('Server error. Please try again later.');
                            break;
                        default:
                            setError(`Error ${error.response.status}: ${error.response.data?.message || 'Failed to fetch posts'}`);
                    }
                } else if (error.request) {
                    setError('Network error. Please check your connection and try again.');
                } else {
                    setError('Error: ' + error.message);
                }
            } finally {
                setLoading(false);
            }
        };

        fetchPostsData();
    }, []);

    // Filtrage des posts
    useEffect(() => {
        let results = posts;
        
        if (searchType === 'author' && searchQuery) {
            results = results.filter(post => {
                if (!post.author) return false;
                
                // Recherche dans le nom d'affichage de l'organisation
                const authorDisplayName = formatAuthorDisplay(post.author);
                return authorDisplayName.toLowerCase().includes(searchQuery.toLowerCase());
            });
        } else if (searchType === 'date' && selectedDate) {
            results = results.filter(post => {
                if (!post.published_at) return false;
                
                const postDate = new Date(post.published_at);
                
                if (searchDateType === 'day') {
                    const postDateStr = postDate.toISOString().split('T')[0];
                    return postDateStr === selectedDate;
                } else if (searchDateType === 'month') {
                    const postMonthStr = `${postDate.getFullYear()}-${String(postDate.getMonth() + 1).padStart(2, '0')}`;
                    return postMonthStr === selectedDate;
                } else if (searchDateType === 'year') {
                    const postYearStr = postDate.getFullYear().toString();
                    return postYearStr === selectedDate;
                }
                return false;
            });
        }
        
        if (selectedAuthor) {
            results = results.filter(post => post.author === selectedAuthor);
        }
    
        setFilteredPosts(results);
        // Reset visible rows quand les résultats changent
        setVisibleRows(new Set());
    }, [posts, searchQuery, selectedAuthor, selectedDate, searchType, searchDateType, formatAuthorDisplay]);

    // Fonctions pour gérer le modal de commentary
    const handleCommentaryClick = useCallback((commentary, post) => {
        setSelectedCommentary({
            text: commentary,
            author: formatAuthorDisplay(post.author),
            publishedAt: post.published_at,
            postId: post.idPost
        });
        setShowCommentaryModal(true);
    }, [formatAuthorDisplay]);

    const closeCommentaryModal = useCallback(() => {
        setShowCommentaryModal(false);
        setSelectedCommentary(null);
    }, []);

    // Fermer le modal avec ESC
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                closeCommentaryModal();
            }
        };

        if (showCommentaryModal) {
            document.addEventListener('keydown', handleEscape);
            document.body.style.overflow = 'hidden';
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = 'unset';
        };
    }, [showCommentaryModal, closeCommentaryModal]);
    
    const handleSearch = (e) => {
        setSearchQuery(e.target.value);
    };

    const handleAuthorChange = (e) => {
        setSelectedAuthor(e.target.value);
    };

    const handleDateChange = (e) => {
        setSelectedDate(e.target.value);
    };

    const handleYearChange = (e) => {
        setSelectedDate(e.target.value);
    };

    const handleMonthYearChange = (e) => {
        setSelectedDate(e.target.value);
    };

    const generateYearOptions = () => {
        const currentYear = new Date().getFullYear();
        const years = [];
        for (let year = currentYear; year >= 2000; year--) {
            years.push(<option key={year} value={year}>{year}</option>);
        }
        return years;
    };

    const generateMonthYearOptions = () => {
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth() + 1;
        const options = [];
        
        for (let year = currentYear; year >= 2000; year--) {
            const months = year === currentYear ? currentMonth : 12;
            for (let month = 1; month <= months; month++) {
                const monthYear = `${year}-${String(month).padStart(2, '0')}`;
                const monthName = new Date(year, month - 1).toLocaleDateString('fr-FR', { month: 'long' });
                options.push(
                    <option key={monthYear} value={monthYear}>
                        {monthName} {year}
                    </option>
                );
            }
        }
        return options;
    };
    
    const handleSearchTypeChange = (e) => {
        setSearchType(e.target.value);
        setSearchQuery('');
        setSelectedDate('');
        setSelectedAuthor('');
    };

    const handleSearchDateTypeChange = (e) => {
        setSearchDateType(e.target.value);
        setSelectedDate('');
    };

    const retryFetch = () => {
        setLoading(true);
        setError(null);
        window.location.reload();
    };

    // Composant pour les médias avec lazy loading amélioré
    const LazyMedia = React.memo(({ post, index }) => {
        const isVisible = visibleRows.has(index.toString());
        const [imageLoaded, setImageLoaded] = useState(false);
        const [videoLoaded, setVideoLoaded] = useState(false);
        
        const hasValidVideo = isValidVideoUrl(post.video_url);

        return (
            <>
                <td className="media-cell">
                    {post.image_url ? (
                        isVisible ? (
                            <img 
                                src={post.image_url} 
                                alt={post.alt_text || "Post image"} 
                                className={`post-image ${imageLoaded ? 'loaded' : ''}`}
                                loading="lazy"
                                onLoad={() => setImageLoaded(true)}
                                onError={(e) => {
                                    e.target.style.display = 'none';
                                    const placeholder = e.target.nextSibling || document.createElement('span');
                                    if (!e.target.nextSibling) {
                                        placeholder.className = 'media-placeholder';
                                        placeholder.innerText = 'No Image';
                                        e.target.parentNode.appendChild(placeholder);
                                    }
                                }}
                            />
                        ) : (
                            <div className="media-skeleton">
                                <div className="skeleton-image"></div>
                                <span>Loading image...</span>
                            </div>
                        )
                    ) : (
                        <span className="media-placeholder">No Image</span>
                    )}
                </td>
                <td className="media-cell">
                    {hasValidVideo ? (
                        isVisible ? (
                            <div className="video-container">
                                <video 
                                    src={post.video_url} 
                                    controls 
                                    className={`post-video ${videoLoaded ? 'loaded' : ''}`}
                                    preload="metadata"
                                    loading="lazy"
                                    onLoadedData={() => setVideoLoaded(true)}
                                    onError={(e) => {
                                        e.target.style.display = 'none';
                                        const placeholder = e.target.nextSibling || document.createElement('span');
                                        if (!e.target.nextSibling) {
                                            placeholder.className = 'media-placeholder';
                                            placeholder.innerText = 'Video Error';
                                            e.target.parentNode.appendChild(placeholder);
                                        }
                                    }}
                                >
                                    Your browser does not support the video tag.
                                </video>
                                <div className="video-controls-hint">Click to play</div>
                            </div>
                        ) : (
                            <div className="media-skeleton">
                                <div className="skeleton-video"></div>
                                <span>Loading video...</span>
                            </div>
                        )
                    ) : (
                        <span className="media-placeholder">No Video</span>
                    )}
                </td>
            </>
        );
    });

    // États de chargement et d'erreur
    if (loading) {
        return (
            <DashboardLayout>
                <div className="loading-container">
                    <div className="loading-content">
                        <div className="loading-spinner"></div>
                        <p>Loading posts...</p>
                        <p className="loading-subtitle">Fetching data from all organizations</p>
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
            <div className="posts-header">
                <h2>All Posts ({filteredPosts.length})</h2>
                <p>Browse and filter posts from all organizations</p>
                {organizations.length > 0 && (
                    <div className="organizations-overview">
                        <div className="organizations-badge">
                            <span className="badge-count">{organizations.length}</span>
                            organizations detected
                        </div>
                        <div className="organization-stats">
                            {organizationStats.slice(0, 3).map(org => (
                                <div key={org.id} className="org-stat-item">
                                    <span className="org-stat-name">{org.displayName}</span>
                                    <span className="org-stat-count">{org.postCount} posts</span>
                                </div>
                            ))}
                            {organizationStats.length > 3 && (
                                <div className="org-stat-more">
                                    +{organizationStats.length - 3} more
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>

            <div className="search-container">
                <div className="search-controls">
                    <select onChange={handleSearchTypeChange} value={searchType} className="search-type-select">
                        <option value="author">Search by Organization</option>
                        <option value="date">Search by Date</option>
                    </select>

                    {searchType === 'author' ? (
                        <div className="author-search-controls">
                            <input 
                                type="text" 
                                placeholder="Search by organization name or ID..." 
                                value={searchQuery} 
                                onChange={handleSearch} 
                                className="search-bar"
                            />
                            <select onChange={handleAuthorChange} value={selectedAuthor} className="category-select">
                                <option value="">All Organizations ({organizations.length})</option>
                                {organizations.map(org => (
                                    <option key={org.id} value={org.id}>
                                        {org.displayName} {organizationStats.find(s => s.id === org.id)?.postCount ? `(${organizationStats.find(s => s.id === org.id).postCount})` : ''}
                                    </option>
                                ))}
                            </select>
                        </div>
                    ) : (
                        <div className="date-search-controls">
                            <select onChange={handleSearchDateTypeChange} value={searchDateType} className="search-date-type-select">
                                <option value="day">Specific Date</option>
                                <option value="month">Month & Year</option>
                                <option value="year">Year Only</option>
                            </select>
                            
                            {searchDateType === 'day' && (
                                <input 
                                    type="date" 
                                    value={selectedDate} 
                                    onChange={handleDateChange} 
                                    className="date-picker"
                                />
                            )}
                            {searchDateType === 'month' && (
                                <select value={selectedDate} onChange={handleMonthYearChange} className="date-picker">
                                    <option value="">Select Month & Year</option>
                                    {generateMonthYearOptions()}
                                </select>
                            )}
                            {searchDateType === 'year' && (
                                <select value={selectedDate} onChange={handleYearChange} className="date-picker">
                                    <option value="">Select Year</option>
                                    {generateYearOptions()}
                                </select>
                            )}
                        </div>
                    )}
                </div>

                {filteredPosts.length !== posts.length && (
                    <div className="search-results-info">
                        <span className="results-count">
                            Showing {filteredPosts.length} of {posts.length} posts
                            {selectedAuthor && (
                                <span className="filter-active">
                                    • Filtered by: {formatAuthorDisplay(selectedAuthor)}
                                </span>
                            )}
                            {searchQuery && (
                                <span className="filter-active">
                                    • Search: "{searchQuery}"
                                </span>
                            )}
                        </span>
                        <button 
                            onClick={() => {
                                setSearchQuery('');
                                setSelectedAuthor('');
                                setSelectedDate('');
                            }} 
                            className="clear-filters-btn"
                        >
                            Clear All Filters
                        </button>
                    </div>
                )}
            </div>

            <div className="posts-table-container" ref={tableContainerRef}>
                {filteredPosts.length === 0 ? (
                    <div className="no-posts-message">
                        <div className="no-posts-icon">📭</div>
                        <h3>No posts found</h3>
                        <p>
                            {posts.length === 0 
                                ? "No posts available in the system." 
                                : "No posts match your current filters."
                            }
                        </p>
                        {posts.length > 0 && (
                            <button 
                                onClick={() => {
                                    setSearchQuery('');
                                    setSelectedAuthor('');
                                    setSelectedDate('');
                                }} 
                                className="clear-filters-btn"
                            >
                                Show All Posts
                            </button>
                        )}
                    </div>
                ) : (
                    <table className="posts-table">
                        <thead>
                            <tr>
                                <th>Organization</th>
                                <th>Published At</th>
                                <th>Last Modified</th>
                                <th>Commentary</th>
                                <th>Alt Text</th>
                                <th>Image</th>
                                <th>Video</th>
                                <th>Status</th>
                                <th>Visibility</th>
                                <th>Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredPosts.map((post, index) => {
                                const postId = post.idPost || index;
                                const commentaryData = formatCommentary(post.commentary, postId);
                                
                                return (
                                    <tr 
                                        key={postId} 
                                        className="fade-in"
                                        data-row-index={index}
                                    >
                                        <td className="author-cell">
                                            <div className="organization-info">
                                                <span className="org-name">{formatAuthorDisplay(post.author)}</span>
                                                {post.author && post.author.includes('urn:li:organization:') && (
                                                    <span className="org-id">
                                                        ID: {post.author.split(':').pop()}
                                                    </span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="date-cell">
                                            {post.published_at ? 
                                                `${new Date(post.published_at).toLocaleDateString('fr-FR')} à ${new Date(post.published_at).toLocaleTimeString('fr-FR')}` : 
                                                'N/A'
                                            }
                                        </td>
                                        <td className="date-cell">
                                            {post.last_modified_at ? 
                                                `${new Date(post.last_modified_at).toLocaleDateString('fr-FR')} à ${new Date(post.last_modified_at).toLocaleTimeString('fr-FR')}` : 
                                                'N/A'
                                            }
                                        </td>
                                        <td className="commentary-cell">
                                            <div className="commentary-content-wrapper">
                                                <div 
                                                    className="commentary-text"
                                                    onClick={() => handleCommentaryClick(post.commentary, post)}
                                                >
                                                    {commentaryData.text}
                                                </div>
                                                {commentaryData.isTruncated && (
                                                    <button 
                                                        className="see-more-btn"
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            toggleCommentaryExpansion(postId);
                                                        }}
                                                    >
                                                        {expandedCommentaries.has(postId) ? 'See less' : 'See more'}
                                                    </button>
                                                )}
                                            </div>
                                        </td>
                                        <td className="alt-text-cell">
                                            {post.alt_text || 'No Alt Text'}
                                        </td>
                                        <LazyMedia post={post} index={index} />
                                        <td className="status-cell">
                                            <span className={`status-badge ${post.lifecycle_state?.toLowerCase() || 'unknown'}`}>
                                                {post.lifecycle_state || 'Unknown'}
                                            </span>
                                        </td>
                                        <td className="visibility-cell">{post.visibility || 'Public'}</td>
                                        <td className="distribution-cell">{post.distribution || 'Standard'}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Modal pour afficher le commentary complet */}
            {showCommentaryModal && selectedCommentary && (
                <div className="commentary-modal-overlay" onClick={closeCommentaryModal}>
                    <div className="commentary-modal-content" onClick={(e) => e.stopPropagation()}>
                        <div className="commentary-modal-header">
                            <h3>Commentary Details</h3>
                            <button className="modal-close-btn" onClick={closeCommentaryModal}>
                                ×
                            </button>
                        </div>
                        <div className="commentary-modal-body">
                            <div className="commentary-meta">
                                <div className="meta-item">
                                    <strong>Organization:</strong> {selectedCommentary.author}
                                </div>
                                {selectedCommentary.postId && (
                                    <div className="meta-item">
                                        <strong>Post ID:</strong> {selectedCommentary.postId}
                                    </div>
                                )}
                                {selectedCommentary.publishedAt && (
                                    <div className="meta-item">
                                        <strong>Published:</strong> {new Date(selectedCommentary.publishedAt).toLocaleString('fr-FR')}
                                    </div>
                                )}
                            </div>
                            <div className="commentary-full-text">
                                {selectedCommentary.text || 'No content available'}
                            </div>
                        </div>
                        <div className="commentary-modal-footer">
                            <button className="modal-close-btn" onClick={closeCommentaryModal}>
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </DashboardLayout>
    );
};

export default OtherPosts;