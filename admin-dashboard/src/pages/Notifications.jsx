import { useState } from 'react';
import { adminApi } from '../api/client';

export default function Notifications() {
  const [form, setForm] = useState({ title: '', body: '', type: 'promotional' });
  const [sent, setSent] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    await adminApi.sendNotification(form);
    setForm({ title: '', body: '', type: 'promotional' });
    setSent(true);
    setTimeout(() => setSent(false), 3000);
  };

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Send Notification</h1>
      <div className="bg-white rounded-xl border p-6 max-w-lg">
        {sent && <div className="bg-green-50 text-green-700 text-sm p-3 rounded-lg mb-4">Notification sent!</div>}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">Title</label>
            <input
              value={form.title}
              onChange={(e) => setForm({ ...form, title: e.target.value })}
              className="w-full border rounded-lg px-3 py-2 text-sm"
              required
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Message</label>
            <textarea
              value={form.body}
              onChange={(e) => setForm({ ...form, body: e.target.value })}
              className="w-full border rounded-lg px-3 py-2 text-sm h-24"
              required
            />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Type</label>
            <select
              value={form.type}
              onChange={(e) => setForm({ ...form, type: e.target.value })}
              className="w-full border rounded-lg px-3 py-2 text-sm"
            >
              <option value="promotional">Promotional</option>
              <option value="order">Order</option>
              <option value="general">General</option>
            </select>
          </div>
          <button type="submit" className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            Send to All Users
          </button>
        </form>
      </div>
    </div>
  );
}
