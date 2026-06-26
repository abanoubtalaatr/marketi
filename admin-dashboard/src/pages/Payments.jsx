import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';
import StatCard from '../components/StatCard';

export default function Payments() {
  const [payments, setPayments] = useState([]);
  const [revenue, setRevenue] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([adminApi.payments(), adminApi.revenue()])
      .then(([payRes, revRes]) => {
        setPayments(payRes.data.data.data || payRes.data.data);
        setRevenue(revRes.data.data);
      })
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="text-slate-500">Loading...</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Payments</h1>
      {revenue && (
        <div className="grid grid-cols-3 gap-4 mb-6">
          <StatCard title="Total Revenue" value={`${Number(revenue.total_revenue).toFixed(2)} SAR`} color="green" />
          <StatCard title="Pending" value={`${Number(revenue.pending_amount).toFixed(2)} SAR`} color="amber" />
          <StatCard title="Refunded" value={`${Number(revenue.refunded_amount).toFixed(2)} SAR`} color="rose" />
        </div>
      )}
      <div className="bg-white rounded-xl border overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 border-b">
            <tr>
              <th className="text-left p-4">Payment #</th>
              <th className="text-left p-4">Customer</th>
              <th className="text-left p-4">Amount</th>
              <th className="text-left p-4">Method</th>
              <th className="text-left p-4">Status</th>
            </tr>
          </thead>
          <tbody>
            {payments.map((p) => (
              <tr key={p.id} className="border-b border-slate-100">
                <td className="p-4 font-medium">{p.payment_number}</td>
                <td className="p-4">{p.user?.name}</td>
                <td className="p-4">{p.amount} SAR</td>
                <td className="p-4">{p.payment_method}</td>
                <td className="p-4">
                  <span className="px-2 py-1 rounded-full bg-slate-100 text-xs">{p.status}</span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
