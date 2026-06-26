import { NavLink } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const links = [
  { to: '/', label: 'Dashboard', icon: '📊' },
  { to: '/users', label: 'Users', icon: '👥' },
  { to: '/categories', label: 'Categories', icon: '📁' },
  { to: '/brands', label: 'Brands', icon: '🏷️' },
  { to: '/products', label: 'Products', icon: '📦' },
  { to: '/orders', label: 'Orders', icon: '🛒' },
  { to: '/payments', label: 'Payments', icon: '💳' },
  { to: '/notifications', label: 'Notifications', icon: '🔔' },
  { to: '/support', label: 'Support', icon: '💬' },
];

export default function Layout({ children }) {
  const { user, logout } = useAuth();

  return (
    <div className="flex min-h-screen">
      <aside className="w-64 bg-slate-900 text-white flex flex-col">
        <div className="p-6 border-b border-slate-700">
          <h1 className="text-xl font-bold">Marketi</h1>
          <p className="text-slate-400 text-sm">Admin Dashboard</p>
        </div>
        <nav className="flex-1 p-4 space-y-1">
          {links.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.to === '/'}
              className={({ isActive }) =>
                `flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition ${
                  isActive ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800'
                }`
              }
            >
              <span>{link.icon}</span>
              {link.label}
            </NavLink>
          ))}
        </nav>
        <div className="p-4 border-t border-slate-700">
          <p className="text-sm text-slate-400 truncate">{user?.email}</p>
          <button
            onClick={logout}
            className="mt-2 w-full text-left text-sm text-red-400 hover:text-red-300"
          >
            Logout
          </button>
        </div>
      </aside>
      <main className="flex-1 overflow-auto">
        <div className="p-8">{children}</div>
      </main>
    </div>
  );
}
