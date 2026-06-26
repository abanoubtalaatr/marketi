import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';

function CrudPage({ title, fetchFn, createFn, deleteFn, fields }) {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState({});
  const [showForm, setShowForm] = useState(false);

  const load = () => fetchFn().then((res) => setItems(res.data.data)).finally(() => setLoading(false));

  useEffect(() => { load(); }, []);

  const handleCreate = async (e) => {
    e.preventDefault();
    await createFn(form);
    setForm({});
    setShowForm(false);
    load();
  };

  const handleDelete = async (id) => {
    if (!confirm('Delete this item?')) return;
    await deleteFn(id);
    load();
  };

  if (loading) return <div className="text-slate-500">Loading...</div>;

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">{title}</h1>
        <button onClick={() => setShowForm(!showForm)} className="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
          {showForm ? 'Cancel' : 'Add New'}
        </button>
      </div>
      {showForm && (
        <form onSubmit={handleCreate} className="bg-white rounded-xl border p-4 mb-4 flex gap-3 flex-wrap">
          {fields.map((f) => (
            <input
              key={f.key}
              placeholder={f.label}
              value={form[f.key] || ''}
              onChange={(e) => setForm({ ...form, [f.key]: e.target.value })}
              className="border rounded-lg px-3 py-2 text-sm flex-1 min-w-[150px]"
              required={f.required}
            />
          ))}
          <button type="submit" className="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">Save</button>
        </form>
      )}
      <div className="bg-white rounded-xl border overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 border-b">
            <tr>
              <th className="text-left p-4">Name</th>
              <th className="text-left p-4">Slug</th>
              <th className="text-left p-4">Products</th>
              <th className="text-left p-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            {items.map((item) => (
              <tr key={item.id} className="border-b border-slate-100">
                <td className="p-4 font-medium">{item.name}</td>
                <td className="p-4 text-slate-500">{item.slug}</td>
                <td className="p-4">{item.products_count ?? 0}</td>
                <td className="p-4">
                  <button onClick={() => handleDelete(item.id)} className="text-red-600 hover:underline text-xs">Delete</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}

export function Categories() {
  return (
    <CrudPage
      title="Categories"
      fetchFn={adminApi.categories}
      createFn={adminApi.createCategory}
      deleteFn={adminApi.deleteCategory}
      fields={[
        { key: 'name', label: 'Name', required: true },
        { key: 'description', label: 'Description' },
      ]}
    />
  );
}

export function Brands() {
  return (
    <CrudPage
      title="Brands"
      fetchFn={adminApi.brands}
      createFn={adminApi.createBrand}
      deleteFn={adminApi.deleteBrand}
      fields={[
        { key: 'name', label: 'Name', required: true },
        { key: 'description', label: 'Description' },
      ]}
    />
  );
}
