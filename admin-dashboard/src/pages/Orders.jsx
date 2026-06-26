import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';

const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

export default function Orders() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminApi.orders().then((res) => setOrders(res.data.data.data || res.data.data)).finally(() => setLoading(false));
  }, []);

  const updateStatus = async (id, status) => {
    await adminApi.updateOrderStatus(id, status);
    setOrders((prev) => prev.map((o) => (o.id === id ? { ...o, status } : o)));
  };

  if (loading) return <div className="text-slate-500">Loading...</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Orders</h1>
      <div className="bg-white rounded-xl border overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 border-b">
            <tr>
              <th className="text-left p-4">Order #</th>
              <th className="text-left p-4">Customer</th>
              <th className="text-left p-4">Total</th>
              <th className="text-left p-4">Payment</th>
              <th className="text-left p-4">Status</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((order) => (
              <tr key={order.id} className="border-b border-slate-100">
                <td className="p-4 font-medium">{order.order_number}</td>
                <td className="p-4">{order.user?.name}</td>
                <td className="p-4">{order.total} SAR</td>
                <td className="p-4">{order.payment_type}</td>
                <td className="p-4">
                  <select
                    value={order.status}
                    onChange={(e) => updateStatus(order.id, e.target.value)}
                    className="border rounded-lg px-2 py-1 text-xs"
                  >
                    {statuses.map((s) => (
                      <option key={s} value={s}>{s}</option>
                    ))}
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
