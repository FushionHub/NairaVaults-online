"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"

export default function WithdrawPage() {
  const [amount, setAmount] = useState("")
  const [bankCode, setBankCode] = useState("")
  const [accountNumber, setAccountNumber] = useState("")
  const [banks, setBanks] = useState<any[]>([])
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/fiat/banks").then(({ data }) => setBanks(data.banks || [])).catch(() => {})
  }, [])

  const handleWithdraw = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/fiat/withdraw", { amount, currency: "NGN", bank_code: bankCode, account_number: accountNumber, gateway: "paystack" })
      alert("Withdrawal initiated")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Withdraw Funds</h1>
      <form onSubmit={handleWithdraw} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Amount (NGN)</label>
          <input type="number" value={amount} onChange={(e) => setAmount(e.target.value)} min="100"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Bank</label>
          <select value={bankCode} onChange={(e) => setBankCode(e.target.value)}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            <option value="">Select bank</option>
            {banks.map((b: any) => <option key={b.code} value={b.code}>{b.name}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Account Number</label>
          <input type="text" value={accountNumber} onChange={(e) => setAccountNumber(e.target.value)} maxLength={10}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Processing..." : "Withdraw"}
        </button>
      </form>
    </div>
  )
}
