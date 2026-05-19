"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import Link from "next/link"
import { CreditCard } from "lucide-react"

export default function CardsPage() {
  const [cards, setCards] = useState<any[]>([])

  useEffect(() => {
    api.get("/cards").then(({ data }) => setCards(data.cards || [])).catch(() => {})
  }, [])

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Virtual Cards</h1>
        <Link href="/cards/create" className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          Create Card
        </Link>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {cards.map((card: any) => (
          <div key={card.id} className="p-6 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 border border-border space-y-4">
            <div className="flex justify-between items-center">
              <CreditCard className="w-8 h-8 text-primary" />
              <span className="text-xs capitalize px-2 py-1 rounded-full bg-accent">{card.card_type}</span>
            </div>
            <p className="text-lg tracking-widest font-mono">{card.masked_pan || "•••• •••• •••• ••••"}</p>
            <div className="flex justify-between text-sm text-muted-foreground">
              <span>{card.expiry_month}/{card.expiry_year}</span>
              <span className={card.is_active ? "text-green-500" : "text-red-500"}>
                {card.is_active ? "Active" : "Frozen"}
              </span>
            </div>
            <p className="text-xl font-bold">{card.balance} {card.currency}</p>
          </div>
        ))}
      </div>
    </div>
  )
}
