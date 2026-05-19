"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import { PiggyBank, Lock, Unlock } from "lucide-react"

export default function SavingsPage() {
  const [plans, setPlans] = useState<any[]>([])
  const [showCreate, setShowCreate] = useState(false)
  const [form, setForm] = useState({ name: "", target_amount: "", duration_months: "6", interest_rate: "8.5" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/savings").then(({ data }) => setPlans(data.plans || [])).catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/savings", form)
      const { data } = await api.get("/savings")
      setPlans(data.plans || [])
      setShowCreate(false)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Savings Plans</h1>
        <button onClick={() => setShowCreate(!showCreate)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showCreate ? "Cancel" : "New Plan"}
        </button>
      </div>
      {showCreate && (
        <form onSubmit={handleCreate} className="p-6 rounded-xl bg-card border border-border space-y-4">
          {Object.entries(form).map(([key, val]) => (
            <div key={key}>
              <label className="block text-sm font-medium mb-1 capitalize">{key.replace("_", " ")}</label>
              <input type={key.includes("amount") || key.includes("rate") || key.includes("months") ? "number" : "text"}
                value={val} onChange={(e) => setForm((p) => ({ ...p, [key]: e.target.value }))}
                className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
            </div>
          ))}
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Creating..." : "Create Plan"}
          </button>
        </form>
      )}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {plans.map((plan: any) => (
          <div key={plan.id} className="p-6 rounded-xl bg-card border border-border space-y-3">
            <div className="flex items-center gap-2">
              <PiggyBank className="w-5 h-5 text-primary" />
              <span className="font-semibold">{plan.name}</span>
              {plan.is_locked ? <Lock className="w-3 h-3 text-yellow-500" /> : <Unlock className="w-3 h-3 text-green-500" />}
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-muted-foreground">Balance</span>
              <span className="font-medium">{plan.current_balance} {plan.currency}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-muted-foreground">Target</span>
              <span>{plan.target_amount} {plan.currency}</span>
            </div>
            <div className="w-full bg-accent rounded-full h-2">
              <div className="bg-primary h-2 rounded-full" style={{ width: `${Math.min(100, (Number(plan.current_balance) / Number(plan.target_amount)) * 100)}%` }} />
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
