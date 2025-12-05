import React, { useEffect, useState } from "react";
import axios from "axios";
import DashboardLayout from "./DashboardLayout"; 
import "./css/UsersManagement.css";

axios.defaults.withCredentials = true; // if using cookies or Sanctum

const UsersManagement = () => {
    const [users, setUsers] = useState([]);
    const [currentUserEmail, setCurrentUserEmail] = useState(""); // logged-in user email

    useEffect(() => {
        // fetch logged-in user profile
        axios.get("http://localhost:8000/api/profile", { withCredentials: true })
            .then((res) => setCurrentUserEmail(res.data.user?.email))
            .catch((err) => console.error(err));

        // fetch users list
        axios.get("http://localhost:8000/api/users", { withCredentials: true })
            .then((res) => setUsers(res.data))
            .catch((err) => console.error(err));
    }, []);

    const toggleRole = async (id) => {
        try {
            const res = await axios.post(`http://localhost:8000/api/users/${id}/toggle-role`);
            setUsers(users.map((u) =>
                u.id === id ? { ...u, role: res.data.role } : u
            ));
        } catch (error) {
            alert(error.response?.data?.message || "Error changing role");
        }
    };

    const toggleBlock = async (id) => {
        try {
            const res = await axios.post(`http://localhost:8000/api/users/${id}/toggle-block`);
            setUsers(users.map(u =>
                u.id === id ? { ...u, blocked: res.data.blocked } : u
            ));
        } catch (error) {
            alert(error.response?.data?.message || "Error blocking/unblocking user");
        }
    };

    return (
        <DashboardLayout>
            <div style={{ padding: "20px" }}>
                <h2 style={{ marginBottom: "20px" }}>User Management</h2>
                <table style={{ width: "100%", borderCollapse: "collapse" }}>
                    <thead>
                        <tr style={{ background: "#f0f0f0" }}>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.map((u) => {
                            const isCurrentUser = u.email === currentUserEmail;
                            return (
                                <tr key={u.id} style={{ borderBottom: "1px solid #ddd" }}>
                                    <td>{u.id}</td>
                                    <td>{u.name}</td>
                                    <td>{u.email}</td>
                                    <td>
                                        <span className={u.role === "admin" ? "badge-admin" : "badge-user"}>
                                            {u.role}
                                        </span>
                                    </td>
                                    <td style={{ color: u.blocked ? "red" : "green" }}>
                                        {u.blocked ? "Blocked" : "Active"}
                                    </td>
                                    <td>
                                        {/* do not show buttons for logged-in user */}
                                        {!isCurrentUser && (
                                            <>
                                                <button
                                                    onClick={() => toggleRole(u.id)}
                                                    className={`action-btn ${u.role === "admin" ? "btn-demote" : "btn-promote"}`}
                                                >
                                                    {u.role === "admin" ? "Revoke Access" : "Admin Access"}
                                                </button>
                                                <button
                                                    onClick={() => toggleBlock(u.id)}
                                                    className={`action-btn ${u.blocked ? "btn-unblock" : "btn-block"}`}
                                                >
                                                    {u.blocked ? "Unblock" : "Block"}
                                                </button>
                                            </>
                                        )}
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </DashboardLayout>
    );
};

export default UsersManagement;
