"use client"
import { useState } from "react"
import api from "@/lib/api"
import { ArrowDownUp } from "lucide-react"

const coins = ["BTC", "ETH", "BNB", "USDT", "SOL", "MATIC"]

export default function CryptoSwapPage() {
  const [fromCoin, setFromCoin] = useState("BTC")
  const [toCoin, setToCoin] = useState("ETH")
  const [amount, setAmount] = useState("")
  const [isLoading, setIsLoading] = useState(false)
  const [result, setResult] = useState<any>(null)

  const handleSwap = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const { data } = await api.post("/crypto/swap", { from_coin: fromCoin, to_coin: toCoin, amount })
      setResult(data)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Swap Crypto</h1>
      <form onSubmit={handleSwap} className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">From</label>
          <select value={fromCoin} onChange={(e) => setFromCoin(e.target.value)}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {coins.map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
        </div>
        <div className="flex justify-center">
          <button type="button" onClick={() => { setFromCoin(toCoin); setToCoin(fromCoin) }}
            className="p-2 rounded-full bg-accent hover:bg-accent/80"><ArrowDownUp className="w-4 h-4" /></button>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">To</label>
          <select value={toCoin} onChange={(e) => setToCoin(e.target.value)}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {coins.filter((c) => c !== fromCoin).map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Amount ({fromCoin})</label>
          <input type="number" value={amount} onChange={(e) => setAmount(e.target.value)} step="0.00000001"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Swapping..." : "Swap"}
        </button>
      </form>
      {result && (
        <div className="p-4 rounded-lg bg-green-500/10 text-green-500 text-sm">
          Received {result.to_amount} {toCoin}
        </div>
      )}
    </div>
  )
}
