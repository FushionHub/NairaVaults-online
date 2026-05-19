"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import Link from "next/link"
import { Headphones, Plus } from "lucide-react"

export default function SupportPage() {
  const [tickets, setTickets] = useState<any[]>([])
  const [showCreate, setShowCreate] = useState(false)
  const [form, setForm] = useState({ subject: "", category: "general", priority: "medium", body: "" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/support/tickets").then(({ data }) => setTickets(data.tickets || [])).catch(() => {})
  }, [])

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/support/tickets", form)
      const { data } = await api.get("/support/tickets")
      setTickets(data.tickets || [])
      setShowCreate(false)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Support</h1>
        <button onClick={() => setShowCreate(!showCreate)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm flex items-center gap-1">
          <Plus className="w-4 h-4" /> New Ticket
        </button>
      </div>
      {showCreate && (
        <form onSubmit={handleCreate} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <input type="text" value={form.subject} onChange={(e) => setForm((p) => ({ ...p, subject: e.target.value }))}
            placeholder="Subject" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <select value={form.category} onChange={(e) => setForm((p) => ({ ...p, category: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["general", "billing", "technical", "account", "trading"].map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
          <textarea value={form.body} onChange={(e) => setForm((p) => ({ ...p, body: e.target.value }))}
            placeholder="Describe your issue" rows={4}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Creating..." : "Create Ticket"}
          </button>
        </form>
      )}
      <div className="space-y-3">
        {tickets.map((ticket: any) => (
          <Link key={ticket.id} href={`/support/tickets/${ticket.id}`}
            className="block p-4 rounded-lg bg-card border border-border hover:border-primary transition">
            <div className="flex justify-between items-center">
              <div className="flex items-center gap-2">
                <Headphones className="w-4 h-4 text-primary" />
                <span className="font-medium">{ticket.subject}</span>
              </div>
              <span className={`px-2 py-1 text-xs rounded-full ${ticket.status === "open" ? "bg-green-500/10 text-green-500" : ticket.status === "closed" ? "bg-accent text-muted-foreground" : "bg-yellow-500/10 text-yellow-500"}`}>
                {ticket.status}
              </span>
            </div>
            <p className="text-sm text-muted-foreground mt-1">{ticket.category} · {new Date(ticket.created_at).toLocaleDateString()}</p>
          </Link>
        ))}
      </div>
    </div>
  )
}
