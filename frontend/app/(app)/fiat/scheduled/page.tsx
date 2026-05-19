"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { Calendar, Trash2 } from "lucide-react"

export default function ScheduledTransfersPage() {
  const [transfers, setTransfers] = useState<any[]>([])
  const [showCreate, setShowCreate] = useState(false)
  const [form, setForm] = useState({ amount: "", currency: "NGN", recipient_email: "", frequency: "monthly", start_date: "" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/fiat/scheduled").then(({ data }) => setTransfers(data.transfers || [])).catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/fiat/scheduled", form)
      const { data } = await api.get("/fiat/scheduled")
      setTransfers(data.transfers || [])
      setShowCreate(false)
    } finally {
      setIsLoading(false)
    }
  }

  const handleCancel = async (id: number) => {
    await api.delete(`/fiat/scheduled/${id}`)
    setTransfers((prev) => prev.filter((t) => t.id !== id))
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Scheduled Transfers</h1>
        <button onClick={() => setShowCreate(!showCreate)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showCreate ? "Cancel" : "Schedule Transfer"}
        </button>
      </div>
      {showCreate && (
        <form onSubmit={handleCreate} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <input type="email" value={form.recipient_email} onChange={(e) => setForm((p) => ({ ...p, recipient_email: e.target.value }))}
            placeholder="Recipient email" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <input type="number" value={form.amount} onChange={(e) => setForm((p) => ({ ...p, amount: e.target.value }))}
            placeholder="Amount" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <select value={form.frequency} onChange={(e) => setForm((p) => ({ ...p, frequency: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["daily", "weekly", "biweekly", "monthly"].map((f) => <option key={f} value={f}>{f}</option>)}
          </select>
          <input type="date" value={form.start_date} onChange={(e) => setForm((p) => ({ ...p, start_date: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Scheduling..." : "Schedule"}
          </button>
        </form>
      )}
      <div className="space-y-3">
        {transfers.map((t: any) => (
          <div key={t.id} className="p-4 rounded-lg bg-card border border-border flex justify-between items-center">
            <div className="flex items-center gap-2">
              <Calendar className="w-4 h-4 text-primary" />
              <span>{t.amount} {t.currency} - {t.frequency}</span>
            </div>
            <button onClick={() => handleCancel(t.id)} className="text-destructive hover:opacity-70"><Trash2 className="w-4 h-4" /></button>
          </div>
        ))}
      </div>
    </div>
  )
}
