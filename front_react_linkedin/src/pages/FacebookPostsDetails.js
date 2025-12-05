import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import DashboardLayoutFacebook from './DashboardLayoutFacebook';
import './css/OtherPosts.css';
import axios from 'axios';

const FacebookPostsDetails = () => {
    const { id } = useParams();
    const [posts, setPosts] = useState([]);
    const [filteredPosts, setFilteredPosts] = useState([]);
    const [error, setError] = useState(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedPage, setSelectedPage] = useState('');
    const [searchType, setSearchType] = useState('page');
    const [searchDateType, setSearchDateType] = useState('day');
    const [userPages, setUserPages] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchPostsData = async () => {
            try {
                const response = await axios.get('http://localhost:8000/pages_postsDatabase', { withCredentials: true });
                setPosts(response.data);
                setFilteredPosts(response.data);
    
                const pagesPromises = response.data.map(async post => {
                    const pageId = post.fb_id.split('_')[0]; 
                    try {
                        const res = await axios.get(`http://localhost:8000/pageName/${pageId}`);
                        return { id: pageId, name: res.data.name };
                    } catch {
                        return { id: pageId, name: 'Unknown Page' };
                    }
                    finally {
                        setLoading(false);
                    } 
                });
    
                const userPages = await Promise.all(pagesPromises);
                const uniquePages = [...new Map(userPages.map(page => [page.id, page])).values()];
                setUserPages(uniquePages);
    
                console.log('Fetched page names:', uniquePages.map(page => page.name)); 
            } catch (error) {
                console.error('Error fetching posts data:', error);
                setError('Failed to load posts data.');
            }
        };
    
        fetchPostsData();
    }, [id]);
    
    useEffect(() => {
        let results = posts;

        if (searchType === 'page' && searchQuery) {
            results = results.filter(post => {
                const extractedString = post.fb_id.split('_')[0];
                return extractedString.toLowerCase().includes(searchQuery.toLowerCase());
            });
        } else if (searchType === 'date') {
            results = results.filter(post => {
                const postDate = new Date(post.updated_time);
                const postDateStr = postDate.toISOString().split('T')[0];
                
                if (searchDateType === 'day') {
                    return postDateStr === searchQuery; 
                } else if (searchDateType === 'month') {
                    return postDateStr.startsWith(searchQuery); 
                } else if (searchDateType === 'year') {
                    return postDate.getFullYear().toString() === searchQuery; 
                }
                return false;
            });
        }
        
        if (selectedPage) {
            results = results.filter(post => post.fb_id.split('_')[0] === selectedPage);
        }

        setFilteredPosts(results);
    }, [searchQuery, selectedPage, searchType, searchDateType, posts]);

    const handleSearch = (e) => {
        setSearchQuery(e.target.value);
    };

    const handlePageChange = (e) => {
        setSelectedPage(e.target.value);
    };

    const generateYearOptions = () => {
        const currentYear = new Date().getFullYear();
        return Array.from({ length: currentYear - 2000 + 1 }, (_, i) => (
            <option key={currentYear - i} value={currentYear - i}>{currentYear - i}</option>
        ));
    };

    const generateMonthYearOptions = () => {
        const currentYear = new Date().getFullYear();
        const months = [];
        for (let year = currentYear; year >= 2000; year--) {
            for (let month = 1; month <= 12; month++) {
                const monthYear = `${year}-${String(month).padStart(2, '0')}`;
                months.push(<option key={monthYear} value={monthYear}>{monthYear}</option>);
            }
        }
        return months;
    };

    const handleSearchTypeChange = (e) => {
        setSearchType(e.target.value);
        setSearchQuery('');
    };

    const handleSearchDateTypeChange = (e) => {
        setSearchDateType(e.target.value);
    };
    if (loading) return <DashboardLayoutFacebook><p>Loading...</p></DashboardLayoutFacebook>

    if (error) return <DashboardLayoutFacebook><div className="posts-details">Error: {error}</div></DashboardLayoutFacebook>;

    return (
        <DashboardLayoutFacebook>
            <div className="search-container">
                <select onChange={handleSearchTypeChange} value={searchType} className="search-type-select">
                    <option value="page">Search by page</option>
                    <option value="date">Search by Date</option>
                </select>

                {searchType === 'page' ? (
                    <>
                        <input
                            type="text"
                            placeholder="Search by page..."
                            value={searchQuery}
                            onChange={handleSearch}
                            className="search-bar"
                        />
                     <select onChange={handlePageChange} value={selectedPage} className="category-select">
    <option value="">All pages</option>
    {userPages.map(({ id, name }) => (
        <option key={id} value={id}>{`${id} - ${name}`}</option>
    ))}
</select>

                    </>
                ) : (
                    <>
                        <select onChange={handleSearchDateTypeChange} value={searchDateType} className="search-date-type-select">
                            <option value="day">Day</option>
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                        {searchDateType === 'day' && (
                            <input
                                type="date"
                                onChange={handleSearch}
                                className="date-picker"
                            />
                        )}
                        {searchDateType === 'month' && (
                            <select value={searchQuery} onChange={handleSearch} className="date-picker">
                                <option value="">Select Month/Year</option>
                                {generateMonthYearOptions()}
                            </select>
                        )}
                        {searchDateType === 'year' && (
                            <select value={searchQuery} onChange={handleSearch} className="date-picker">
                                <option value="">Select Year</option>
                                {generateYearOptions()}
                            </select>
                        )}
                    </>
                )}
            </div>
            <div className="posts-table-container">
                <table className="posts-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Published At</th>
                            <th>Last Modified At</th>
                            <th>Status Type</th>
                            <th>Privacy</th>
                            <th>Pictures</th>
                            <th>Video</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filteredPosts.map((post, index) => (
                            <tr key={index} className="fade-in">
                                <td>{post.description}</td>
                                <td>{new Date(post.created_time).toLocaleString('fr-FR')}</td>
                                <td>{new Date(post.updated_time).toLocaleString('fr-FR')}</td>
                                <td>{post.status_type || 'No Content'}</td>
                                <td>
                                    {(() => {
                                        try {
                                            const privacy = JSON.parse(post.privacy);
                                            return privacy.value || 'No Content';
                                        } catch {
                                            return 'No Content';
                                        }
                                    })()}
                                </td>
                                <td>
                                    {(() => {
                                        try {
                                            const pictures = JSON.parse(post.pictures);
                                            return Array.isArray(pictures) && pictures.length > 0 ? (
                                                pictures.map((picture, picIndex) => (
                                                    // eslint-disable-next-line jsx-a11y/img-redundant-alt
                                                    <img key={picIndex} src={picture} alt={`Post Image ${picIndex}`} className="post-image" />
                                                ))
                                            ) : (
                                                'No Image'
                                            );
                                        } catch {
                                            return 'No Image';
                                        }
                                    })()}
                                </td>
                                <td>
                                    {(() => {
                                        try {
                                            const videos = JSON.parse(post.videos);
                                            return Array.isArray(videos) && videos.length > 0 ? (
                                                videos.map((video, vidIndex) => (
                                                    <video key={vidIndex} src={video} controls className="post-video"></video>
                                                ))
                                            ) : (
                                                'No Video'
                                            );
                                        } catch {
                                            return 'No Video'; 
                                        }
                                    })()}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </DashboardLayoutFacebook>
    );
};

export default FacebookPostsDetails;
