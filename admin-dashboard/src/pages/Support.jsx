import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';

export default function Support() {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [replying, setReplying] = useState(null);
  const [reply, setReply] = useState('');

  useEffect(() => {
    adminApi.supportTickets().then((res) => setTickets(res.data.data.data || res.data.data)).finally(() => setLoading(false));
  }, []);

  const handleReply = async (id) => {
    await adminApi.replyTicket(id, reply);
    setReplying(null);
    setReply('');
    const res = await adminApi.supportTickets();
    setTickets(res.data.data.data || res.data.data);
  };

  if (loading) return <div className="text-slate-500">Loading...</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Support Tickets</h1>
      <div className="space-y-4">
        {tickets.map((ticket) => (
          <div key={ticket.id} className="bg-white rounded-xl border p-4">
            <div className="flex justify-between items-start">
              <div>
                <p className="font-medium">{ticket.subject}</p>
                <p className="text-sm text-slate-500">{ticket.ticket_number} · {ticket.user?.name}</p>
              </div>
              <span className="text-xs px-2 py-1 rounded-full bg-slate-100">{ticket.status}</span>
            </div>
            <p className="text-sm mt-2 text-slate-700">{ticket.message}</p>
            {ticket.admin_reply && (
              <div className="mt-2 p-2 bg-indigo-50 rounded-lg text-sm">
                <strong>Reply:</strong> {ticket.admin_reply}
              </div>
            )}
            {ticket.status === 'open' && (
              <div className="mt-3">
                {replying === ticket.id ? (
                  <div className="flex gap-2">
                    <input
                      value={reply}
                      onChange={(e) => setReply(e.target.value)}
                      className="flex-1 border rounded-lg px-3 py-1.5 text-sm"
                      placeholder="Type your reply..."
                    />
                    <button onClick={() => handleReply(ticket.id)} className="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-sm">Send</button>
                  </div>
                ) : (
                  <button onClick={() => setReplying(ticket.id)} className="text-indigo-600 text-sm hover:underline">Reply</button>
                )}
              </div>
            )}
          </div>
        ))}
        {tickets.length === 0 && <p className="text-slate-500">No support tickets yet.</p>}
      </div>
    </div>
  );
}
