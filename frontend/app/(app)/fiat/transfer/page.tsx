"use client"
import { useState } from "react"
import api from "@/lib/api"

export default function TransferPage() {
  const [amount, setAmount] = useState("")
  const [toEmail, setToEmail] = useState("")
  const [isLoading, setIsLoading] = useState(false)

  const handleTransfer = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/fiat/transfer", { amount, from_currency: "NGN", to_email: toEmail })
      alert("Transfer completed")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Transfer Funds</h1>
      <form onSubmit={handleTransfer} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Recipient Email</label>
          <input type="email" value={toEmail} onChange={(e) => setToEmail(e.target.value)}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Amount (NGN)</label>
          <input type="number" value={amount} onChange={(e) => setAmount(e.target.value)} min="1"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Transferring..." : "Transfer"}
        </button>
      </form>
    </div>
  )
}
