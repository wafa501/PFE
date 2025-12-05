/* eslint-disable no-unused-vars */
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import DashboardLayoutFacebook from './DashboardLayoutFacebook';
import { Bar } from 'react-chartjs-2';
import './css/ReactionsFacebookPage.css'; 

const ReactionsFacebookPage = () => {
    const [reactions, setReactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pages, setPages] = useState([]);
    const [commentsData, setCommentsData] = useState({}); 
    const [colors, setColors] = useState({});

    const reactionColors = {
        like: 'rgba(255, 99, 132, 0.6)', 
        love: 'rgba(255, 159, 64, 0.6)', 
        wow: 'rgba(75, 192, 192, 0.6)', 
        haha: 'rgba(153, 102, 255, 0.6)', 
        sad: 'rgba(255, 206, 86, 0.6)', 
        angry: 'rgba(255, 0, 0, 0.6)' 
    };

    useEffect(() => {
        const fetchReactions = async () => {
            try {
                const response = await axios.get('http://localhost:8000/reactionspages_postsDatabase', { withCredentials: true });
                setReactions(response.data);

                const pageIds = [...new Set(response.data.map(stat => stat.post_id.split('_')[0]))];
                const pagePromises = pageIds.map(async (pageId) => {
                    try {
                        const res = await axios.get(`http://localhost:8000/pageName/${pageId}`);
                        return { id: pageId, name: res.data.name };
                    } catch {
                        return { id: pageId, name: 'Page inconnue' };
                    }
                });

                const pagesData = await Promise.all(pagePromises);
                setPages(pagesData);

                const colorMap = {};
                pagesData.forEach((page, index) => {
                    colorMap[page.id] = `hsl(${index * 36}, 70%, 50%)`;
                });
                setColors(colorMap);

                const commentsPromises = pageIds.map(async (pageId) => {
                    try {
                        const res = await axios.get(`http://localhost:8000/comments/${pageId}`); 
                        return { pageId, comments: res.data }; 
                    } catch {
                        return { pageId, comments: [] };
                    }
                });

                const commentsData = await Promise.all(commentsPromises);
                const commentsMap = {};
                commentsData.forEach(({ pageId, comments }) => {
                    commentsMap[pageId] = comments; 
                });
                setCommentsData(commentsMap);
                
            } catch (error) {
                setError(error.response?.data?.message || 'Erreur lors de la récupération des réactions.');
            } finally {
                setLoading(false);
            }
        };

        fetchReactions();
    }, []);

    if (loading) {
        return <div>Chargement...</div>;
    }

    if (error) {
        return <div>{error}</div>;
    }

    const groupedReactions = reactions.reduce((acc, reaction) => {
        const pageId = reaction.post_id.split('_')[0];
        if (!acc[pageId]) {
            acc[pageId] = {
                pageId,
                like_count: 0,
                love_count: 0,
                wow_count: 0,
                haha_count: 0,
                sad_count: 0,
                angry_count: 0,
                comments_count: 0,
                total_reactions: 0,
            };
        }
        acc[pageId].like_count += reaction.like_count;
        acc[pageId].love_count += reaction.love_count;
        acc[pageId].wow_count += reaction.wow_count;
        acc[pageId].haha_count += reaction.haha_count;
        acc[pageId].sad_count += reaction.sad_count;
        acc[pageId].angry_count += reaction.angry_count;
        acc[pageId].comments_count += reaction.comments_count;
        acc[pageId].total_reactions += reaction.total_reactions;
        return acc;
    }, {});

    const finalReactions = Object.values(groupedReactions);

    const labels = finalReactions.map(reaction => {
        const page = pages.find(p => p.id === reaction.pageId);
        return page ? `${page.name} 📄` : 'Inconnu 📄';
    });

    const likeCounts = finalReactions.map(reaction => reaction.like_count);
    const loveCounts = finalReactions.map(reaction => reaction.love_count);
    const wowCounts = finalReactions.map(reaction => reaction.wow_count);
    const hahaCounts = finalReactions.map(reaction => reaction.haha_count);
    const sadCounts = finalReactions.map(reaction => reaction.sad_count);
    const angryCounts = finalReactions.map(reaction => reaction.angry_count);

    const data = {
        labels,
        datasets: [
            {
                label: 'Likes ❤️',
                data: likeCounts,
                backgroundColor: likeCounts.map(() => reactionColors.like),
            },
            {
                label: 'Love 💕',
                data: loveCounts,
                backgroundColor: loveCounts.map(() => reactionColors.love),
            },
            {
                label: 'Wow 😮',
                data: wowCounts,
                backgroundColor: wowCounts.map(() => reactionColors.wow),
            },
            {
                label: 'Haha 😂',
                data: hahaCounts,
                backgroundColor: hahaCounts.map(() => reactionColors.haha),
            },
            {
                label: 'Sad 😢',
                data: sadCounts,
                backgroundColor: sadCounts.map(() => reactionColors.sad),
            },
            {
                label: 'Angry 😡',
                data: angryCounts,
                backgroundColor: angryCounts.map(() => reactionColors.angry),
            },
        ],
    };

    return (
        <DashboardLayoutFacebook>
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
                <h1>Détails des Réactions</h1>
                <div style={{ display: 'flex', justifyContent: 'space-between', width: '100%' }}>
                    <div style={{ flex: 1 }}>
                        <Bar
                            data={data}
                            options={{
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                    },
                                },
                            }}
                        />
                    </div>
                    <div style={{ flex: 0.3, paddingLeft: '20px' }}>
                        <h3>Total Réactions</h3>
                        {finalReactions.map(reaction => (
                            <div key={reaction.pageId}>
                                <h4>{pages.find(p => p.id === reaction.pageId)?.name || 'Inconnu'}:</h4>
                                <p>Total: {reaction.total_reactions}</p>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Comments Table */}
                {/* Comments Table */}
<div style={{ marginTop: '40px', width: '100%' }}>
    <h2>Commentaires</h2>
    <table style={{ width: '100%', borderCollapse: 'collapse' }} className="comments-table">
        <thead>
            <tr>
                <th>Nom de la Page</th>
                <th>Nombre de Commentaires</th>
                <th>Commentaires</th>
            </tr>
        </thead>
        <tbody>
            {finalReactions.map(reaction => {
                const page = pages.find(p => p.id === reaction.pageId);
                const comments = reaction.comments_count; 
                
                return (
                    <tr key={reaction.pageId}>
                        <td>{page ? page.name : 'Inconnu'}</td>
                        <td>{comments}</td>
                        <td className="comments-cell">
                            {comments > 0 ? (
                                <ul>
                           
                                    <li>{`Commentaires pour ${page ? page.name : 'Inconnu'}`}</li>
                                </ul>
                            ) : (
                                <ul><li>Aucun commentaire</li></ul>
                            )}
                        </td>
                    </tr>
                );
            })}
        </tbody>
    </table>
</div>


            </div>
        </DashboardLayoutFacebook>
    );
};

export default ReactionsFacebookPage;
