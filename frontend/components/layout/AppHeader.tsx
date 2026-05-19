"use client"

import { useAuthStore } from "@/store/authStore"
import { LogOut, User, Menu } from "lucide-react"
import Link from "next/link"
import { useRouter } from "next/navigation"

export default function AppHeader() {
  const { user, logout } = useAuthStore()
  const router = useRouter()

  const handleLogout = async () => {
    await logout()
    router.push("/sign-in")
  }

  return (
    <header className="sticky top-0 z-50 bg-card/80 backdrop-blur border-b border-border px-6 py-3 flex items-center justify-between">
      <button className="lg:hidden p-2">
        <Menu className="w-5 h-5" />
      </button>

      <div className="flex-1" />

      <div className="flex items-center gap-4">
        <Link href="/settings" className="flex items-center gap-2 text-sm">
          <User className="w-4 h-4" />
          <span className="hidden sm:inline">{user?.display_name || user?.email}</span>
        </Link>
        <button onClick={handleLogout} className="p-2 hover:bg-accent rounded-lg" title="Logout">
          <LogOut className="w-4 h-4" />
        </button>
      </div>
    </header>
  )
}
