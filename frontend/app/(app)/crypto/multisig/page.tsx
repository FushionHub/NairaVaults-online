"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { Users, Plus, CheckCircle } from "lucide-react"

export default function MultiSigPage() {
  const [wallets, setWallets] = useState<any[]>([])
  const [showCreate, setShowCreate] = useState(false)
  const [form, setForm] = useState({ name: "", required_signatures: "2", signatories: ["", ""] })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/crypto/multisig").then(({ data }) => setWallets(data.wallets || [])).catch(() => {})
  }, [])

  const addSignatory = () => setForm((p) => ({ ...p, signatories: [...p.signatories, ""] }))
  const updateSignatory = (i: number, val: string) => {
    setForm((p) => ({ ...p, signatories: p.signatories.map((s, idx) => idx === i ? val : s) }))
  }

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/crypto/multisig", { ...form, signatories: form.signatories.filter(Boolean) })
      const { data } = await api.get("/crypto/multisig")
      setWallets(data.wallets || [])
      setShowCreate(false)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Multi-Signature Wallets</h1>
        <button onClick={() => setShowCreate(!showCreate)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showCreate ? "Cancel" : "New Multi-Sig"}
        </button>
      </div>
      {showCreate && (
        <form onSubmit={handleCreate} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <input type="text" value={form.name} onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))}
            placeholder="Wallet name" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <div>
            <label className="block text-sm font-medium mb-1">Required Signatures</label>
            <input type="number" value={form.required_signatures} onChange={(e) => setForm((p) => ({ ...p, required_signatures: e.target.value }))}
              min="2" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          </div>
          <div className="space-y-2">
            <label className="block text-sm font-medium">Signatories</label>
            {form.signatories.map((s, i) => (
              <input key={i} type="text" value={s} onChange={(e) => updateSignatory(i, e.target.value)}
                placeholder={`Signatory ${i + 1} address or email`}
                className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
            ))}
            <button type="button" onClick={addSignatory} className="text-primary text-sm flex items-center gap-1">
              <Plus className="w-3 h-3" /> Add signatory
            </button>
          </div>
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Creating..." : "Create Multi-Sig Wallet"}
          </button>
        </form>
      )}
      <div className="space-y-4">
        {wallets.map((w: any) => (
          <div key={w.id} className="p-6 rounded-xl bg-card border border-border space-y-3">
            <div className="flex items-center gap-2"><Users className="w-5 h-5 text-primary" /><span className="font-semibold">{w.name || "Multi-Sig Wallet"}</span></div>
            <p className="text-xs font-mono text-muted-foreground">{w.wallet_address}</p>
            <div className="flex items-center gap-2 text-sm"><CheckCircle className="w-3 h-3" />{w.required_signatures} of {w.signatories?.length || 0} signatures required</div>
          </div>
        ))}
      </div>
    </div>
  )
}
