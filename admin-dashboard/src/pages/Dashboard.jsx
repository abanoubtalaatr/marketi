import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';
import StatCard from '../components/StatCard';

export default function Dashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminApi.dashboard()
      .then((res) => setStats(res.data.data))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="text-slate-500">Loading dashboard...</div>;
  if (!stats) return <div className="text-red-500">Failed to load dashboard</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Dashboard</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <StatCard title="Total Users" value={stats.total_users} color="indigo" />
        <StatCard title="Total Products" value={stats.total_products} color="green" />
        <StatCard title="Total Orders" value={stats.total_orders} color="amber" />
        <StatCard
          title="Total Revenue"
          value={`${Number(stats.total_revenue).toFixed(2)} SAR`}
          subtitle={`${stats.pending_orders} pending orders`}
          color="rose"
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-xl border border-slate-200 p-6">
          <h2 className="font-semibold mb-4">Recent Orders</h2>
          <div className="space-y-3">
            {stats.recent_orders?.map((order) => (
              <div key={order.id} className="flex justify-between items-center text-sm border-b border-slate-100 pb-2">
                <div>
                  <p className="font-medium">{order.order_number}</p>
                  <p className="text-slate-500">{order.user?.name}</p>
                </div>
                <div className="text-right">
                  <p className="font-medium">{order.total} SAR</p>
                  <span className="text-xs px-2 py-0.5 rounded-full bg-slate-100">{order.status}</span>
                </div>
              </div>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-6">
          <h2 className="font-semibold mb-4">Best Selling Products</h2>
          <div className="space-y-3">
            {stats.best_selling_products?.map((product) => (
              <div key={product.id} className="flex justify-between items-center text-sm">
                <span>{product.name}</span>
                <span className="text-slate-500">{product.rating_count} reviews</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
