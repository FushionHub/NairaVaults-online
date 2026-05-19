"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import Link from "next/link"

export default function P2PPage() {
  const [offers, setOffers] = useState<any[]>([])
  const [direction, setDirection] = useState("buy")

  useEffect(() => {
    api.get(`/p2p/offers?direction=${direction}`).then(({ data }) => setOffers(data.offers || [])).catch(() => {})
  }, [direction])

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">P2P Trading</h1>
        <Link href="/trading/p2p/create" className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          Create Offer
        </Link>
      </div>
      <div className="flex gap-2">
        {["buy", "sell"].map((d) => (
          <button key={d} onClick={() => setDirection(d)}
            className={`px-4 py-2 rounded-lg text-sm capitalize transition ${direction === d ? "bg-primary text-primary-foreground" : "bg-accent"}`}>
            {d}
          </button>
        ))}
      </div>
      <div className="space-y-3">
        {offers.map((offer: any) => (
          <Link key={offer.id} href={`/trading/p2p/${offer.id}`}
            className="block p-4 rounded-lg bg-card border border-border hover:border-primary transition">
            <div className="flex justify-between items-center">
              <div>
                <span className="font-medium">{offer.coin_symbol}</span>
                <span className="ml-2 text-sm text-muted-foreground">{offer.amount} @ {offer.rate_per_unit}</span>
              </div>
              <span className={`px-2 py-1 text-xs rounded-full ${offer.status === "open" ? "bg-green-500/10 text-green-500" : "bg-accent"}`}>
                {offer.status}
              </span>
            </div>
          </Link>
        ))}
        {offers.length === 0 && <p className="text-center text-muted-foreground py-8">No offers found</p>}
      </div>
    </div>
  )
}
