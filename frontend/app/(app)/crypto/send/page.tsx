"use client"
import { useState } from "react"
import api from "@/lib/api"

export default function CryptoSendPage() {
  const [coinSymbol, setCoinSymbol] = useState("BTC")
  const [amount, setAmount] = useState("")
  const [toAddress, setToAddress] = useState("")
  const [isLoading, setIsLoading] = useState(false)

  const handleSend = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/crypto/send", { coin_symbol: coinSymbol, amount, to_address: toAddress })
      alert("Transfer initiated")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Send Crypto</h1>
      <form onSubmit={handleSend} className="space-y-4">
        <select value={coinSymbol} onChange={(e) => setCoinSymbol(e.target.value)}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background">
          {["BTC", "ETH", "BNB", "USDT", "SOL", "MATIC"].map((c) => <option key={c} value={c}>{c}</option>)}
        </select>
        <div>
          <label className="block text-sm font-medium mb-1">Recipient Address</label>
          <input type="text" value={toAddress} onChange={(e) => setToAddress(e.target.value)}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Amount</label>
          <input type="number" value={amount} onChange={(e) => setAmount(e.target.value)} step="0.00000001"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Sending..." : "Send"}
        </button>
      </form>
    </div>
  )
}
