import { useEffect, useState } from 'react';
import { adminApi } from '../api/client';

export default function Products() {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    adminApi.products().then((res) => {
      setProducts(res.data.data.data || res.data.data);
    }).finally(() => setLoading(false));
  }, []);

  const handleDelete = async (id) => {
    if (!confirm('Delete this product?')) return;
    await adminApi.deleteProduct(id);
    setProducts((prev) => prev.filter((p) => p.id !== id));
  };

  if (loading) return <div className="text-slate-500">Loading...</div>;

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Products</h1>
      <div className="bg-white rounded-xl border overflow-hidden">
        <table className="w-full text-sm">
          <thead className="bg-slate-50 border-b">
            <tr>
              <th className="text-left p-4">Name</th>
              <th className="text-left p-4">Category</th>
              <th className="text-left p-4">Brand</th>
              <th className="text-left p-4">Price</th>
              <th className="text-left p-4">Stock</th>
              <th className="text-left p-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            {products.map((p) => (
              <tr key={p.id} className="border-b border-slate-100">
                <td className="p-4 font-medium">{p.name}</td>
                <td className="p-4 text-slate-500">{p.category?.name}</td>
                <td className="p-4 text-slate-500">{p.brand?.name}</td>
                <td className="p-4">{p.price} SAR</td>
                <td className="p-4">{p.stock_quantity}</td>
                <td className="p-4">
                  <button onClick={() => handleDelete(p.id)} className="text-red-600 hover:underline text-xs">Delete</button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
