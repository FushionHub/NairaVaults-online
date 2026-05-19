"use client"
import { useState } from "react"
import api from "@/lib/api"

const coins = ["BTC", "ETH", "BNB", "USDT", "SOL", "MATIC"]

export default function CryptoBuyPage() {
  const [coinSymbol, setCoinSymbol] = useState("BTC")
  const [amountFiat, setAmountFiat] = useState("")
  const [currency, setCurrency] = useState("NGN")
  const [isLoading, setIsLoading] = useState(false)
  const [result, setResult] = useState<any>(null)

  const handleBuy = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const { data } = await api.post("/crypto/buy", { coin_symbol: coinSymbol, amount_fiat: amountFiat, currency })
      setResult(data)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Buy Crypto</h1>
      <form onSubmit={handleBuy} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Select Coin</label>
          <div className="grid grid-cols-3 gap-2">
            {coins.map((c) => (
              <button key={c} type="button" onClick={() => setCoinSymbol(c)}
                className={`p-3 rounded-lg border text-sm transition ${coinSymbol === c ? "border-primary bg-primary/10 text-primary" : "border-input"}`}>
                {c}
              </button>
            ))}
          </div>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Amount ({currency})</label>
          <input type="number" value={amountFiat} onChange={(e) => setAmountFiat(e.target.value)} min="100"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <select value={currency} onChange={(e) => setCurrency(e.target.value)}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background">
          <option value="NGN">NGN</option>
          <option value="USD">USD</option>
        </select>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Buying..." : `Buy ${coinSymbol}`}
        </button>
      </form>
      {result && (
        <div className="p-4 rounded-lg bg-green-500/10 text-green-500 text-sm">
          Purchased {result.crypto_amount} {coinSymbol}. Ref: {result.reference}
        </div>
      )}
    </div>
  )
}
