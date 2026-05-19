"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { RefreshCw } from "lucide-react"

export default function DcaPage() {
  const [plans, setPlans] = useState<any[]>([])
  const [showCreate, setShowCreate] = useState(false)
  const [form, setForm] = useState({ coin_symbol: "BTC", amount: "", currency: "NGN", frequency: "weekly" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/crypto/dca").then(({ data }) => setPlans(data.plans || [])).catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/crypto/dca", form)
      const { data } = await api.get("/crypto/dca")
      setPlans(data.plans || [])
      setShowCreate(false)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Dollar Cost Averaging</h1>
        <button onClick={() => setShowCreate(!showCreate)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showCreate ? "Cancel" : "New DCA Plan"}
        </button>
      </div>
      {showCreate && (
        <form onSubmit={handleCreate} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <select value={form.coin_symbol} onChange={(e) => setForm((p) => ({ ...p, coin_symbol: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["BTC", "ETH", "BNB", "SOL", "MATIC"].map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
          <input type="number" value={form.amount} onChange={(e) => setForm((p) => ({ ...p, amount: e.target.value }))}
            placeholder="Amount per purchase" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <select value={form.frequency} onChange={(e) => setForm((p) => ({ ...p, frequency: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["daily", "weekly", "biweekly", "monthly"].map((f) => <option key={f} value={f}>{f}</option>)}
          </select>
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Creating..." : "Start DCA"}
          </button>
        </form>
      )}
      <div className="space-y-3">
        {plans.map((plan: any) => (
          <div key={plan.id} className="p-4 rounded-lg bg-card border border-border flex justify-between items-center">
            <div className="flex items-center gap-2">
              <RefreshCw className="w-4 h-4 text-primary" />
              <span>{plan.coin_symbol} - {plan.amount} {plan.currency} / {plan.frequency}</span>
            </div>
            <span className={`text-xs px-2 py-1 rounded-full ${plan.status === "active" ? "bg-green-500/10 text-green-500" : "bg-accent"}`}>
              {plan.status}
            </span>
          </div>
        ))}
      </div>
    </div>
  )
}
