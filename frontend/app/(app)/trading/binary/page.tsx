"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { TrendingUp, TrendingDown, Clock } from "lucide-react"

export default function BinaryTradingPage() {
  const [asset, setAsset] = useState("BTCUSDT")
  const [stake, setStake] = useState("")
  const [direction, setDirection] = useState<"up" | "down">("up")
  const [duration, setDuration] = useState(60)
  const [currentPrice, setCurrentPrice] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(false)
  const [activeTrade, setActiveTrade] = useState<any>(null)

  useEffect(() => {
    const interval = setInterval(async () => {
      try {
        const { data } = await api.get(`/trading/binary/price?symbol=${asset}`)
        setCurrentPrice(data.price)
      } catch { /* */ }
    }, 5000)
    return () => clearInterval(interval)
  }, [asset])

  const handleTrade = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      const { data } = await api.post("/trading/binary", { asset_symbol: asset, direction, stake_amount: stake, duration_seconds: duration })
      setActiveTrade(data.trade)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Binary Trading</h1>
      <div className="p-6 rounded-xl bg-card border border-border">
        <div className="flex items-center justify-between mb-4">
          <select value={asset} onChange={(e) => setAsset(e.target.value)}
            className="px-4 py-2 rounded-lg border border-input bg-background">
            {["BTCUSDT", "ETHUSDT", "BNBUSDT", "SOLUSDT"].map((a) => <option key={a} value={a}>{a}</option>)}
          </select>
          <span className="text-2xl font-bold text-primary">{currentPrice ? `$${currentPrice}` : "Loading..."}</span>
        </div>
        <div className="h-48 bg-accent rounded-lg mb-4 flex items-center justify-center text-muted-foreground">
          TradingView Chart Area
        </div>
      </div>
      <form onSubmit={handleTrade} className="p-6 rounded-xl bg-card border border-border space-y-4">
        <div className="grid grid-cols-2 gap-2">
          <button type="button" onClick={() => setDirection("up")}
            className={`p-4 rounded-lg border flex items-center justify-center gap-2 transition ${direction === "up" ? "border-green-500 bg-green-500/10 text-green-500" : "border-input"}`}>
            <TrendingUp className="w-5 h-5" /> UP
          </button>
          <button type="button" onClick={() => setDirection("down")}
            className={`p-4 rounded-lg border flex items-center justify-center gap-2 transition ${direction === "down" ? "border-red-500 bg-red-500/10 text-red-500" : "border-input"}`}>
            <TrendingDown className="w-5 h-5" /> DOWN
          </button>
        </div>
        <div>
          <label className="block text-sm font-medium mb-1">Stake (USDT)</label>
          <input type="number" value={stake} onChange={(e) => setStake(e.target.value)} min="1"
            className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
        </div>
        <div>
          <label className="block text-sm font-medium mb-1 flex items-center gap-1"><Clock className="w-3 h-3" /> Duration</label>
          <div className="grid grid-cols-4 gap-2">
            {[30, 60, 120, 300].map((d) => (
              <button key={d} type="button" onClick={() => setDuration(d)}
                className={`p-2 rounded-lg border text-sm transition ${duration === d ? "border-primary bg-primary/10" : "border-input"}`}>
                {d}s
              </button>
            ))}
          </div>
        </div>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Placing trade..." : "Place Trade"}
        </button>
      </form>
      {activeTrade && (
        <div className="p-4 rounded-lg bg-accent text-sm">
          Trade active: {activeTrade.direction} on {activeTrade.asset_symbol} | Stake: {activeTrade.stake_amount} | Entry: {activeTrade.entry_price}
        </div>
      )}
    </div>
  )
}
