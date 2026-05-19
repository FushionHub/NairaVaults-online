"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { HandCoins } from "lucide-react"

export default function LoansPage() {
  const [loans, setLoans] = useState<any[]>([])
  const [showApply, setShowApply] = useState(false)
  const [form, setForm] = useState({ amount: "", currency: "NGN", tenure_months: "3", purpose: "" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/loans").then(({ data }) => setLoans(data.loans || [])).catch(() => {})
  }, [])

  const handleApply = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/loans/request", form)
      const { data } = await api.get("/loans")
      setLoans(data.loans || [])
      setShowApply(false)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Loans</h1>
        <button onClick={() => setShowApply(!showApply)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showApply ? "Cancel" : "Apply for Loan"}
        </button>
      </div>
      {showApply && (
        <form onSubmit={handleApply} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <div>
            <label className="block text-sm font-medium mb-1">Amount</label>
            <input type="number" value={form.amount} onChange={(e) => setForm((p) => ({ ...p, amount: e.target.value }))}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Tenure (months)</label>
            <select value={form.tenure_months} onChange={(e) => setForm((p) => ({ ...p, tenure_months: e.target.value }))}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background">
              {[3, 6, 12, 24].map((m) => <option key={m} value={m}>{m} months</option>)}
            </select>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">Purpose</label>
            <textarea value={form.purpose} onChange={(e) => setForm((p) => ({ ...p, purpose: e.target.value }))}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" rows={3} />
          </div>
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Applying..." : "Submit Application"}
          </button>
        </form>
      )}
      <div className="space-y-4">
        {loans.map((loan: any) => (
          <div key={loan.id} className="p-6 rounded-xl bg-card border border-border space-y-2">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2"><HandCoins className="w-5 h-5 text-primary" /><span className="font-semibold">{loan.amount} {loan.currency}</span></div>
              <span className={`px-2 py-1 text-xs rounded-full ${loan.status === "approved" ? "bg-green-500/10 text-green-500" : loan.status === "pending" ? "bg-yellow-500/10 text-yellow-500" : "bg-red-500/10 text-red-500"}`}>{loan.status}</span>
            </div>
            <div className="flex justify-between text-sm text-muted-foreground">
              <span>Interest: {loan.interest_rate}%</span>
              <span>Total: {loan.total_repayable} {loan.currency}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
