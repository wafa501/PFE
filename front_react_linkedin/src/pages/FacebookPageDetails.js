import React, { useEffect, useState } from 'react';
import axios from 'axios';
import DashboardLayoutFacebook from './DashboardLayoutFacebook';
import './css/FacebookPageDetails.css'; 
import { 
    FaClock, FaTag, FaLink, FaPhone, 
    FaMoneyBillWave, FaClipboardList, FaHeart,
    FaUsers, FaMapMarkerAlt, FaEnvelope, FaStar
} from 'react-icons/fa';

const FacebookPageDetails = () => {
    const [pageDetails, setPageDetails] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [selectedPageId, setSelectedPageId] = useState('');

    useEffect(() => {
        const fetchPageDetails = async () => {
            try {
                setLoading(true);
                const response = await axios.get('http://localhost:8000/facebook-pageDetails', { 
                    withCredentials: true 
                });
                setPageDetails(response.data);
                if (response.data.length > 0) {
                    setSelectedPageId(response.data[0].fb_id);
                }
            } catch (err) {
                console.error(err);
                setError('Failed to fetch page details. Please try again later.');
            } finally {
                setLoading(false);
            }
        };

        fetchPageDetails();
    }, []);

    const handlePageChange = (event) => {
        setSelectedPageId(event.target.value);
    };

    const formatHours = (hours) => {
        if (!hours) return [];
        const formattedHours = [];
        const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        const dayKeys = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];

        for (let day = 0; day < 7; day++) {
            const dayKeyOpen = `${dayKeys[day]}_1_open`;
            const dayKeyClose = `${dayKeys[day]}_1_close`;

            if (hours[dayKeyOpen] && hours[dayKeyClose]) {
                formattedHours.push({
                    day: days[day],
                    open: hours[dayKeyOpen],
                    close: hours[dayKeyClose],
                });
            }
        }
        return formattedHours;
    };

    const renderProducts = (products) => {
        if (!products || products.length === 0) return 'No products listed';
        
        try {
            const parsedProducts = typeof products === 'string' ? JSON.parse(products) : products;
            return (
                <div className="products-list">
                    {parsedProducts.slice(0, 5).map((product, index) => (
                        <span key={index} className="product-tag">{product}</span>
                    ))}
                    {parsedProducts.length > 5 && (
                        <span className="product-tag more-tag">+{parsedProducts.length - 5} more</span>
                    )}
                </div>
            );
        } catch {
            return products;
        }
    };

    if (loading) return (
        <DashboardLayoutFacebook>
            <div className="container">
                <div className="loading">
                    <div className="spinner"></div>
                    <span>Loading Facebook Pages...</span>
                </div>
            </div>
        </DashboardLayoutFacebook>
    );

    if (error) return (
        <DashboardLayoutFacebook>
            <div className="container">
                <div className="error-message">
                    ⚠️ {error}
                </div>
            </div>
        </DashboardLayoutFacebook>
    );

    const selectedPage = pageDetails.find(page => page.fb_id === selectedPageId);
    const hoursTable = selectedPage ? formatHours(selectedPage.hours) : [];

    return (
        <DashboardLayoutFacebook>
            <div className="container">
                <h1 className="page-title">📱 Facebook Page Details</h1>
                
                <div className="page-select-container">
                    <label htmlFor="pageSelect">Select Facebook Page</label>
                    <div className="select-wrapper">
                        <select 
                            id="pageSelect" 
                            value={selectedPageId} 
                            onChange={handlePageChange}
                            aria-label="Select Facebook page"
                        >
                            {pageDetails.map((page) => (
                                <option key={page.fb_id} value={page.fb_id}>
                                    {page.name} ({page.category})
                                </option>
                            ))}
                        </select>
                    </div>
                </div>
    
                {selectedPage && (
                    <div className="">
                        <div className="detail-card">
                            <h2>
                                {selectedPage.name}
                                {selectedPage.verified && <FaStar style={{ color: '#4267B2', marginLeft: '10px' }} />}
                            </h2>
                            
                            <div className="card-content">
                                {/* Basic Info */}
                                <div className="card-item">
                                    <FaHeart className="icon" />
                                    <span className="small-title">Fan Count</span>
                                    <div className="card-item-content">
                                        <span className="fan-count">
                                            {selectedPage.fan_count?.toLocaleString() || '0'}
                                            <span className="stats-badge">👥</span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div className="card-item">
                                    <FaTag className="icon" />
                                    <span className="small-title">Category</span>
                                    <div className="card-item-content">
                                        <span>{selectedPage.category || 'N/A'}</span>
                                    </div>
                                </div>
                                
                                <div className="card-item">
                                    <FaLink className="icon" />
                                    <span className="small-title">Website</span>
                                    <div className="card-item-content">
                                        {selectedPage.website ? (
                                            <a 
                                                href={selectedPage.website} 
                                                target="_blank" 
                                                rel="noopener noreferrer"
                                                title="Visit website"
                                            >
                                                {selectedPage.website.length > 30 
                                                    ? selectedPage.website.substring(0, 30) + '...' 
                                                    : selectedPage.website}
                                            </a>
                                        ) : 'N/A'}
                                    </div>
                                </div>
                                
                                <div className="card-item">
                                    <FaPhone className="icon" />
                                    <span className="small-title">Phone</span>
                                    <div className="card-item-content">
                                        <span>{selectedPage.phone || 'N/A'}</span>
                                    </div>
                                </div>
                                
                                <div className="card-item">
                                    <FaMoneyBillWave className="icon" />
                                    <span className="small-title">Price Range</span>
                                    <div className="card-item-content">
                                        <span>{selectedPage.price_range || 'N/A'}</span>
                                    </div>
                                </div>
                                
                                <div className="card-item">
                                    <FaClipboardList className="icon" />
                                    <span className="small-title">Mission</span>
                                    <div className="card-item-content">
                                        <span className="mission-text">
                                            {selectedPage.mission || 'No mission statement available'}
                                        </span>
                                    </div>
                                </div>
                                
                                {/* Products */}
                                <div className="card-item products-item">
                                    <FaClipboardList className="icon" />
                                    <span className="small-title">Products</span>
                                    <div className="card-item-content">
                                        {renderProducts(selectedPage.products)}
                                    </div>
                                </div>
                            </div>
                            
                            {/* Hours Section - Full Width */}
                            <div className="hours-section">
                                <div className="card-item">
                                    <FaClock className="icon" />
                                    <span className="small-title">Business Hours</span>
                                    <div className="card-item-content"></div>
                                </div>
                                
                                {hoursTable.length > 0 ? (
                                    <div className="table-container">
                                        <table className="hours-table">
                                            <thead>
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Opening Time</th>
                                                    <th>Closing Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {hoursTable.map((hour, index) => (
                                                    <tr key={index}>
                                                        <td>{hour.day}</td>
                                                        <td>{hour.open}</td>
                                                        <td>{hour.close}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <div className="no-hours">
                                        No business hours available
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
                
                {pageDetails.length > 0 && (
                    <div className="pages-count">
                        Showing 1 of {pageDetails.length} connected pages
                    </div>
                )}
            </div>
        </DashboardLayoutFacebook>
    );
};

export default FacebookPageDetails;