import React from 'react';
import { Routes, Route, Navigate, useLocation } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import Profile from './pages/ProfilePage';
import Login from './pages/LoginPage';
import OrganizationSelectionPage from "./pages/OrganizationSelectionPage";
import MyOrganizationDetails from './pages/MyOrganizationDetails';
import OtherOrganizationDetails from './pages/OtherOrganizationDetails';
import MyPostsPage from './pages/MyPostsPage';
import OtherPosts from './pages/OtherPosts';
import StatisticsPage from './pages/StatisticsPage';
//import Notifications from './components/Notifications';
import withAuth from './components/withAuth'; 
import LogoutButton from './components/LogoutButton';
import SocialMediaChoice from "./pages/SocialMediaChoice";
import DashboardFacebook from "./pages/DashboardFacebook";
import FacebookPostsDetails from "./pages/FacebookPostsDetails";
import FacebookStatistics from "./pages/FacebookStatistics";
import FacebookProfile from "./pages/FacebookProfile";
import FacebookPageDetails from "./pages/FacebookPageDetails";
import ReactionsFacebookPage from "./pages/ReactionsFacebookPage";
import FacebookbenchStats from "./pages/FacebookbenchStats";
import AddDelListOrgLinkedin from "./pages/AddDelListOrgLinkedin";
import PredictionPage from './pages/PredictionPage';
import UserManagementController from './pages/UsersManagement';
const App = () => {
    const location = useLocation();

   /* const noNotificationPages = ['/login', '/choice' , '/FacebookProfile' , '/FacebookPageDetails',

        '/FacebookPostsDetails' , '/FacebookPostsDetails' , '/FacebookPostsDetails' , '/FacebookStatistics' , '/ReactionsFacebookPage',
        '/' , '/DashboardFacebook' , '/FacebookbenchStats'

     ];

    const isNoNotificationPage = noNotificationPages.includes(location.pathname);
    */
    return (
            <Routes>
            <Route path="/ReactionsFacebookPage" element={<ReactionsFacebookPage />} />
            <Route path="/FacebookbenchStats" element={<FacebookbenchStats />} />

            <Route path="/FacebookProfile" element={<FacebookProfile />} />
            <Route path="/FacebookPageDetails" element={<FacebookPageDetails />} />

                <Route path="/login" element={<Login />} />
                <Route path="/LogoutButton" element={<LogoutButton />} />
                <Route path="/choice" element={<SocialMediaChoice />} />
                <Route path="/DashboardFacebook" element={<DashboardFacebook />} />
                <Route path="/FacebookPostsDetails" element={<FacebookPostsDetails />} />
                <Route path="/FacebookStatistics" element={<FacebookStatistics />} />

                <Route path="/" element={<Navigate to="/choice" />} />

                <Route path="/dashboard" element={withAuth(Dashboard)()} />
                <Route path="/profile" element={withAuth(Profile)()} />
                <Route path="/OrganizationSelectionPage" element={withAuth(OrganizationSelectionPage)()} />
                <Route path="/organization/:id" element={withAuth(MyOrganizationDetails)()} />
                <Route path="/OtherOrganization/:id" element={withAuth(OtherOrganizationDetails)()} />
                <Route path="/MyPostsPage/:id" element={withAuth(MyPostsPage)()} />
                <Route path="/posts" element={withAuth(OtherPosts)()} />
                <Route path="/statistics" element={withAuth(StatisticsPage)()} />
               <Route path="/AddDelListOrgLinkedin" element={withAuth(AddDelListOrgLinkedin)()} />
                <Route path="/prediction" element={withAuth(PredictionPage)()} />
<Route path="/GestionUsers" element={withAuth(UserManagementController)()} />
                
                
            </Routes>
    );
};

export default App;
