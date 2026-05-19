"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { cn } from "@/lib/utils"
import {
  LayoutDashboard, Wallet, ArrowLeftRight, BarChart3, CreditCard,
  PiggyBank, TrendingUp, HandCoins, Shield, Headphones,
  FileText, Bot, Mic, Settings, Medal, Users, Bell,
} from "lucide-react"

const navItems = [
  { href: "/dashboard", icon: LayoutDashboard, label: "Dashboard" },
  { href: "/fiat/deposit", icon: Wallet, label: "Fiat Banking" },
  { href: "/crypto/buy", icon: ArrowLeftRight, label: "Crypto" },
  { href: "/trading/binary", icon: BarChart3, label: "Trading" },
  { href: "/cards", icon: CreditCard, label: "Virtual Cards" },
  { href: "/savings", icon: PiggyBank, label: "Savings" },
  { href: "/investments", icon: TrendingUp, label: "Investments" },
  { href: "/loans", icon: HandCoins, label: "Loans" },
  { href: "/kyc", icon: Shield, label: "KYC" },
  { href: "/tier", icon: Medal, label: "Tier" },
  { href: "/referrals", icon: Users, label: "Referrals" },
  { href: "/analytics", icon: BarChart3, label: "Analytics" },
  { href: "/receipts", icon: FileText, label: "Receipts" },
  { href: "/ai", icon: Bot, label: "AI Assistant" },
  { href: "/voice", icon: Mic, label: "Voice" },
  { href: "/support", icon: Headphones, label: "Support" },
  { href: "/settings", icon: Settings, label: "Settings" },
]

export default function AppSidebar() {
  const pathname = usePathname()

  return (
    <aside className="hidden lg:flex flex-col w-64 bg-card border-r border-border h-screen sticky top-0">
      <div className="p-6">
        <Link href="/dashboard" className="text-2xl font-bold text-primary">
          NairaVault
        </Link>
      </div>

      <nav className="flex-1 overflow-y-auto px-3 space-y-1">
        {navItems.map((item) => {
          const isActive = pathname.startsWith(item.href)
          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                "flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition",
                isActive
                  ? "bg-primary/10 text-primary font-medium"
                  : "text-muted-foreground hover:bg-accent hover:text-foreground"
              )}
            >
              <item.icon className="w-4 h-4" />
              {item.label}
            </Link>
          )
        })}
      </nav>

      <div className="p-4 border-t border-border">
        <Link
          href="/notifications"
          className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
        >
          <Bell className="w-4 h-4" />
          Notifications
        </Link>
      </div>
    </aside>
  )
}
