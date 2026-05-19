"use client"
import { useState } from "react"
import api from "@/lib/api"

export default function DepositPage() {
  const [amount, setAmount] = useState("")
  const [currency, setCurrency] = useState("NGN")
  const [gateway, setGateway] = useState("korapay")
  const [isLoading, setIsLoading] = useState(false)
  const [result, setResult] = useState<any>(null)

  const handleDeposit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const { data } = await api.post("/fiat/deposit", { amount, currency, gateway })
      setResult(data)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Deposit Funds</h1>
      <form onSubmit={handleDeposit} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Amount</label>
          <input type="number" value={amount} onChange={(e) => setAmount(e.target.value)} min="100" step="0.01"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Currency</label>
          <select value={currency} onChange={(e) => setCurrency(e.target.value)}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["NGN", "USD", "EUR", "GBP", "GHS"].map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Payment Gateway</label>
          <div className="grid grid-cols-2 gap-2">
            {["korapay", "paystack", "flutterwave", "paypal"].map((g) => (
              <button key={g} type="button" onClick={() => setGateway(g)}
                className={`p-3 rounded-lg border text-sm capitalize transition ${gateway === g ? "border-primary bg-primary/10 text-primary" : "border-input"}`}>
                {g}
              </button>
            ))}
          </div>
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Processing..." : "Deposit"}
        </button>
      </form>
      {result && (
        <div className="p-4 rounded-lg bg-green-500/10 text-green-500 text-sm">
          Deposit initiated. Reference: {result.reference}
        </div>
      )}
    </div>
  )
}
