import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';

export default function Users() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminApi.users().then((res) => setUsers(res.data.data.data || res.data.data)).finally(() => setLoading(false));
  }, []);

  const toggleActive = async (user) => {
    await adminApi.updateUser(user.id, { is_active: !user.is_active });
    setUsers((prev) => prev.map((u) => (u.id === user.id ? { ...u, is_active: !u.is_active } : u)));
  };

  if (loading) return <div className="text-slate-500">Loading...</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Users</h1>
      <div className="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 border-b">
            <tr>
              <th className="text-left p-4 font-medium">Name</th>
              <th className="text-left p-4 font-medium">Email</th>
              <th className="text-left p-4 font-medium">Role</th>
              <th className="text-left p-4 font-medium">Status</th>
              <th className="text-left p-4 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            {users.map((user) => (
              <tr key={user.id} className="border-b border-slate-100">
                <td className="p-4">{user.name}</td>
                <td className="p-4 text-slate-500">{user.email}</td>
                <td className="p-4">
                  <span className="px-2 py-1 rounded-full bg-slate-100 text-xs">{user.role}</span>
                </td>
                <td className="p-4">
                  <span className={`px-2 py-1 rounded-full text-xs ${user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                    {user.is_active ? 'Active' : 'Blocked'}
                  </span>
                </td>
                <td className="p-4">
                  <button onClick={() => toggleActive(user)} className="text-indigo-600 hover:underline text-xs">
                    {user.is_active ? 'Block' : 'Activate'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
