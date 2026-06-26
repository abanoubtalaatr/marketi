export default function StatCard({ title, value, subtitle, color = 'indigo' }) {
  const colors = {
    indigo: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    green: 'bg-green-50 text-green-700 border-green-200',
    amber: 'bg-amber-50 text-amber-700 border-amber-200',
    rose: 'bg-rose-50 text-rose-700 border-rose-200',
  };

  return (
    <div className={`rounded-xl border p-6 ${colors[color]}`}>
      <p className="text-sm font-medium opacity-80">{title}</p>
      <p className="text-3xl font-bold mt-2">{value}</p>
      {subtitle && <p className="text-xs mt-1 opacity-70">{subtitle}</p>}
    </div>
  );
}
