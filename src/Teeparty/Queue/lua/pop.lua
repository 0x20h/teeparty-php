--[[
Pop a pending item from one of the requsted channels and register
the item for the given worker.
KEYS: the channels
ARGS: worker_id
]]--
local worker_id = ARGV[1]
for i, channel in ipairs(KEYS) do
    local msg = redis.call('rpop', KEYS[i])

    if msg then
        -- read task_id from task
        local task = cjson.decode(msg)
        local task_id = task.id
        
        -- register that worker_id processes task
        redis.call('hmset', 'worker.' .. worker_id, 'current_task', task_id)
        return msg
    end
end

return 0
