"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { Bell, Trash2 } from "lucide-react"

export default function PriceAlertsPage() {
  const [alerts, setAlerts] = useState<any[]>([])
  const [showCreate, setShowCreate] = useState(false)
  const [form, setForm] = useState({ coin_symbol: "BTC", target_price: "", condition: "above" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/crypto/alerts").then(({ data }) => setAlerts(data.alerts || [])).catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/crypto/alerts", form)
      const { data } = await api.get("/crypto/alerts")
      setAlerts(data.alerts || [])
      setShowCreate(false)
    } finally {
      setIsLoading(false)
    }
  }

  const handleDelete = async (id: number) => {
    await api.delete(`/crypto/alerts/${id}`)
    setAlerts((prev) => prev.filter((a) => a.id !== id))
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Price Alerts</h1>
        <button onClick={() => setShowCreate(!showCreate)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showCreate ? "Cancel" : "New Alert"}
        </button>
      </div>
      {showCreate && (
        <form onSubmit={handleCreate} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <select value={form.coin_symbol} onChange={(e) => setForm((p) => ({ ...p, coin_symbol: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["BTC", "ETH", "BNB", "SOL", "MATIC"].map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
          <input type="number" value={form.target_price} onChange={(e) => setForm((p) => ({ ...p, target_price: e.target.value }))}
            placeholder="Target price (USD)" step="0.01" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <div className="grid grid-cols-2 gap-2">
            {["above", "below"].map((c) => (
              <button key={c} type="button" onClick={() => setForm((p) => ({ ...p, condition: c }))}
                className={`p-3 rounded-lg border capitalize text-sm ${form.condition === c ? "border-primary bg-primary/10" : "border-input"}`}>
                {c}
              </button>
            ))}
          </div>
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Creating..." : "Set Alert"}
          </button>
        </form>
      )}
      <div className="space-y-3">
        {alerts.map((alert: any) => (
          <div key={alert.id} className="p-4 rounded-lg bg-card border border-border flex justify-between items-center">
            <div className="flex items-center gap-2">
              <Bell className="w-4 h-4 text-primary" />
              <span>{alert.coin_symbol} {alert.condition} ${alert.target_price}</span>
            </div>
            <button onClick={() => handleDelete(alert.id)} className="text-destructive hover:opacity-70"><Trash2 className="w-4 h-4" /></button>
          </div>
        ))}
      </div>
    </div>
  )
}
